package redis

import (
	"fmt"
	"sync"
	"sync/atomic"
)

// ConnLimiter tracks the number of active TCP connections per client (by runID).
type ConnLimiter struct {
	mu       sync.Mutex
	counters map[string]*atomic.Int64
	limits   map[string]int // max TCP per runID
}

// NewConnLimiter creates an empty ConnLimiter.
func NewConnLimiter() *ConnLimiter {
	return &ConnLimiter{
		counters: make(map[string]*atomic.Int64),
		limits:   make(map[string]int),
	}
}

// SetLimit sets the max TCP connection limit for a given runID.
func (l *ConnLimiter) SetLimit(runID string, maxTCP int) {
	l.mu.Lock()
	defer l.mu.Unlock()
	l.limits[runID] = maxTCP
	if _, ok := l.counters[runID]; !ok {
		l.counters[runID] = &atomic.Int64{}
	}
}

// Incr atomically increments the connection count for runID.
// Returns an error if the count would exceed the configured max.
func (l *ConnLimiter) Incr(runID string) error {
	l.mu.Lock()
	counter, ok := l.counters[runID]
	max := l.limits[runID]
	l.mu.Unlock()

	if !ok {
		l.mu.Lock()
		counter = &atomic.Int64{}
		l.counters[runID] = counter
		max = l.limits[runID]
		l.mu.Unlock()
	}

	if max > 0 && counter.Load() >= int64(max) {
		return fmt.Errorf("max TCP connections (%d) reached for client %s", max, runID)
	}
	counter.Add(1)
	return nil
}

// Decr atomically decrements the connection count for runID.
func (l *ConnLimiter) Decr(runID string) {
	l.mu.Lock()
	counter, ok := l.counters[runID]
	l.mu.Unlock()
	if ok {
		counter.Add(-1)
	}
}

// Remove cleans up the counter and limit for a given runID.
func (l *ConnLimiter) Remove(runID string) {
	l.mu.Lock()
	defer l.mu.Unlock()
	delete(l.counters, runID)
	delete(l.limits, runID)
}
