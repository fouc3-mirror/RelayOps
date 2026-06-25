package redis

import (
	"context"
	"fmt"
	"sync"

	goredis "github.com/redis/go-redis/v9"
)

// Config holds Redis connection configuration.
type Config struct {
	Addr     string `json:"addr"`
	Password string `json:"password,omitempty"`
	DB       int    `json:"db,omitempty"`
}

var (
	globalClient *goredis.Client
	mu           sync.RWMutex
)

// Init creates and tests a Redis connection.
func Init(cfg Config) error {
	if cfg.Addr == "" {
		cfg.Addr = "localhost:6379"
	}

	c := goredis.NewClient(&goredis.Options{
		Addr:     cfg.Addr,
		Password: cfg.Password,
		DB:       cfg.DB,
	})

	if err := c.Ping(context.Background()).Err(); err != nil {
		c.Close()
		return fmt.Errorf("redis ping failed: %w", err)
	}

	mu.Lock()
	globalClient = c
	mu.Unlock()
	return nil
}

// Client returns the global Redis client. Returns nil if not initialized.
func Client() *goredis.Client {
	mu.RLock()
	defer mu.RUnlock()
	return globalClient
}

// Close shuts down the Redis connection.
func Close() error {
	mu.Lock()
	defer mu.Unlock()
	if globalClient != nil {
		err := globalClient.Close()
		globalClient = nil
		return err
	}
	return nil
}
