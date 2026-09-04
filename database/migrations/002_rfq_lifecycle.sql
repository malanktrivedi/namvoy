USE namvoy;

ALTER TABLE trip_requests
  ADD INDEX idx_trip_expiry_status (expires_at,status);

ALTER TABLE provider_bids
  ADD INDEX idx_bid_valid_status (valid_until,status);
