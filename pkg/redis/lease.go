package redis

import (
	"context"
	"encoding/json"
	"fmt"
	"strconv"
	"strings"
	"time"

	goredis "github.com/redis/go-redis/v9"

	"github.com/fouc3-mirror/RelayOps/pkg/util/log"
)

// LeaseConfig holds per-client lease configuration fetched from Redis.
type LeaseConfig struct {
	UserID       int64
	ClientID     int64
	Status       int
	ExpireTime   int64
	MaxTCP       int
	MaxBandwidth int64  // bytes/sec, TODO: actual rate limiting not yet implemented
	AllowedPorts string // e.g. "20000-30000"
}

// IsExpired returns true if the lease has expired.
func (lc *LeaseConfig) IsExpired() bool {
	return time.Now().Unix() > lc.ExpireTime
}

// ValidatePort checks if a port is within the allowed range.
func (lc *LeaseConfig) ValidatePort(port int) error {
	if lc.AllowedPorts == "" {
		return nil
	}
	ranges := strings.SplitSeq(lc.AllowedPorts, ",")
	for r := range ranges {
		r = strings.TrimSpace(r)
		parts := strings.SplitN(r, "-", 2)
		if len(parts) == 1 {
			p, err := strconv.Atoi(parts[0])
			if err != nil {
				continue
			}
			if port == p {
				return nil
			}
		} else if len(parts) == 2 {
			lo, err1 := strconv.Atoi(strings.TrimSpace(parts[0]))
			hi, err2 := strconv.Atoi(strings.TrimSpace(parts[1]))
			if err1 != nil || err2 != nil {
				continue
			}
			if port >= lo && port <= hi {
				return nil
			}
		}
	}
	return fmt.Errorf("port %d is not in allowed range: %s", port, lc.AllowedPorts)
}

// FetchLease reads the lease configuration from Redis hash frp:auth:{nodeID}:{token}.
func FetchLease(ctx context.Context, nodeID, token string) (*LeaseConfig, error) {
	c := Client()
	if c == nil {
		return nil, fmt.Errorf("redis not initialized")
	}

	key := fmt.Sprintf("frp:auth:%s:%s", nodeID, token)
	data, err := c.HGetAll(ctx, key).Result()
	if err != nil {
		return nil, fmt.Errorf("redis HGETALL %s: %w", key, err)
	}
	if len(data) == 0 {
		return nil, fmt.Errorf("lease not found: %s", key)
	}

	lc := &LeaseConfig{}
	if v, ok := data["user_id"]; ok {
		lc.UserID, _ = strconv.ParseInt(v, 10, 64)
	}
	if v, ok := data["client_id"]; ok {
		lc.ClientID, _ = strconv.ParseInt(v, 10, 64)
	}
	if v, ok := data["status"]; ok {
		lc.Status, _ = strconv.Atoi(v)
	}
	if v, ok := data["expire_time"]; ok {
		lc.ExpireTime, _ = strconv.ParseInt(v, 10, 64)
	}
	if v, ok := data["max_tcp"]; ok {
		lc.MaxTCP, _ = strconv.Atoi(v)
	}
	if v, ok := data["max_bandwidth"]; ok {
		lc.MaxBandwidth, _ = ParseBandwidth(v)
	}
	if v, ok := data["allowed_ports"]; ok {
		lc.AllowedPorts = v
	}

	if lc.Status != 1 {
		return nil, fmt.Errorf("lease status is %d (not active)", lc.Status)
	}
	if lc.IsExpired() {
		return nil, fmt.Errorf("lease expired at %d", lc.ExpireTime)
	}

	return lc, nil
}

// ParseBandwidth parses a bandwidth string like "10mb", "1gb", "500kb" into bytes/sec.
// TODO: actual bandwidth rate limiting is not yet implemented. This only parses the value.
func ParseBandwidth(s string) (int64, error) {
	s = strings.ToLower(strings.TrimSpace(s))
	if s == "" {
		return 0, nil
	}

	multipliers := map[string]int64{
		"b":   1,
		"kb":  1024,
		"mb":  1024 * 1024,
		"gb":  1024 * 1024 * 1024,
		"kib": 1024,
		"mib": 1024 * 1024,
		"gib": 1024 * 1024 * 1024,
	}

	for suffix, mult := range multipliers {
		if numStr, ok := strings.CutSuffix(s, suffix); ok {
			num, err := strconv.ParseFloat(numStr, 64)
			if err != nil {
				return 0, fmt.Errorf("invalid bandwidth value: %s", s)
			}
			return int64(num * float64(mult)), nil
		}
	}

	// no suffix, treat as raw bytes
	num, err := strconv.ParseInt(s, 10, 64)
	if err != nil {
		return 0, fmt.Errorf("invalid bandwidth value: %s", s)
	}
	return num, nil
}

// KickMessage represents a control message received from Redis.
type KickMessage struct {
	Action  string `json:"action"`
	Reason  string `json:"reason"`
	Ts      int64  `json:"ts"`
}

// WatchCtrlChannel listens on frp:ctrl:{nodeID}:{clientID} for kick commands.
// It uses BRPOP for blocking read. When a kick message is received, onKick is called.
// The function runs until ctx is cancelled.
func WatchCtrlChannel(ctx context.Context, nodeID string, clientID int64, onKick func(reason string)) {
	c := Client()
	if c == nil {
		return
	}

	key := fmt.Sprintf("frp:ctrl:%s:%d", nodeID, clientID)
	for {
		select {
		case <-ctx.Done():
			return
		default:
		}

		// BRPOP with 5-second timeout so we can check ctx periodically
		result, err := c.BRPop(ctx, 5*time.Second, key).Result()
		if err != nil {
			if err == goredis.Nil || ctx.Err() != nil {
				continue
			}
			log.Warnf("redis BRPOP %s error: %v", key, err)
			continue
		}
		if len(result) < 2 {
			continue
		}

		var msg KickMessage
		if err := json.Unmarshal([]byte(result[1]), &msg); err != nil {
			log.Warnf("invalid kick message on %s: %v", key, err)
			continue
		}
		if msg.Action == "kick" {
			log.Infof("received kick command on %s: reason=%s", key, msg.Reason)
			onKick(msg.Reason)
			return
		}
	}
}
