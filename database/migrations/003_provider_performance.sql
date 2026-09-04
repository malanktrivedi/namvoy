USE namvoy;

ALTER TABLE providers
  ADD COLUMN namvoy_score DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER cancellation_rate,
  ADD COLUMN completed_bookings INT UNSIGNED NOT NULL DEFAULT 0 AFTER namvoy_score,
  ADD COLUMN successful_bookings INT UNSIGNED NOT NULL DEFAULT 0 AFTER completed_bookings,
  ADD COLUMN complaint_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER successful_bookings;

CREATE TABLE IF NOT EXISTS provider_request_views (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  provider_id INT UNSIGNED NOT NULL,
  trip_request_id INT UNSIGNED NOT NULL,
  first_viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  view_count INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_provider_request_view (provider_id, trip_request_id),
  KEY idx_provider_views_provider (provider_id, last_viewed_at),
  KEY idx_provider_views_request (trip_request_id, last_viewed_at),
  CONSTRAINT fk_provider_views_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
  CONSTRAINT fk_provider_views_request FOREIGN KEY (trip_request_id) REFERENCES trip_requests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
