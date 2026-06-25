package redis

import (
	"context"
	"strconv"
	"sync"
	"time"

	goredis "github.com/redis/go-redis/v9"

	"github.com/fouc3-mirror/RelayOps/pkg/util/log"
)

const (
	StreamKey     = "stream:traffic"
	FlushInterval = 5 * time.Second
	MaxStreamLen  = 10000
)

// TrafficBuffer is a thread-safe buffer that accumulates traffic deltas
// per proxy name between flush intervals.
type TrafficBuffer struct {
	mu      sync.Mutex
	entries map[string]int64
}

// NewTrafficBuffer creates an empty TrafficBuffer.
func NewTrafficBuffer() *TrafficBuffer {
	return &TrafficBuffer{
		entries: make(map[string]int64),
	}
}

// Add accumulates delta bytes for a given proxy name.
func (b *TrafficBuffer) Add(proxyName string, delta int64) {
	if delta <= 0 {
		return
	}
	b.mu.Lock()
	b.entries[proxyName] += delta
	b.mu.Unlock()
}

// FlushAndPublish drains the buffer and publishes each proxy's delta
// to the Redis stream as a separate XADD entry.
func (b *TrafficBuffer) FlushAndPublish(ctx context.Context) error {
	b.mu.Lock()
	if len(b.entries) == 0 {
		b.mu.Unlock()
		return nil
	}
	// swap out the map
	pending := b.entries
	b.entries = make(map[string]int64, len(pending))
	b.mu.Unlock()

	c := Client()
	if c == nil {
		return nil
	}

	now := strconv.FormatInt(time.Now().Unix(), 10)
	for proxy, delta := range pending {
		if err := c.XAdd(ctx, &goredis.XAddArgs{
			Stream: StreamKey,
			MaxLen: MaxStreamLen,
			Approx: true,
			Values: map[string]interface{}{
				"proxy": proxy,
				"delta": strconv.FormatInt(delta, 10),
				"ts":    now,
			},
		}).Err(); err != nil {
			log.Warnf("redis XADD failed for proxy %s: %v", proxy, err)
			// best-effort: continue with other entries
		}
	}
	return nil
}

// StartFlushLoop runs a goroutine that calls FlushAndPublish every FlushInterval.
// It returns immediately. The loop stops when ctx is cancelled.
func StartFlushLoop(ctx context.Context, buffer *TrafficBuffer) {
	go func() {
		ticker := time.NewTicker(FlushInterval)
		defer ticker.Stop()

		for {
			select {
			case <-ctx.Done():
				// final flush
				_ = buffer.FlushAndPublish(context.Background())
				return
			case <-ticker.C:
				if err := buffer.FlushAndPublish(ctx); err != nil {
					log.Warnf("traffic flush error: %v", err)
				}
			}
		}
	}()
}
