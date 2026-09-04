USE namvoy;

ALTER TABLE providers
  ADD COLUMN typical_min_budget DECIMAL(12,2) NULL AFTER description,
  ADD COLUMN typical_max_budget DECIMAL(12,2) NULL AFTER typical_min_budget;

CREATE TABLE provider_destinations (
  provider_id INT UNSIGNED NOT NULL,
  destination_id INT UNSIGNED NOT NULL,
  expertise_level ENUM('standard','expert') NOT NULL DEFAULT 'standard',
  PRIMARY KEY (provider_id,destination_id),
  CONSTRAINT fk_provider_destination_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE,
  CONSTRAINT fk_provider_destination_destination FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE provider_categories (
  provider_id INT UNSIGNED NOT NULL,
  category VARCHAR(80) NOT NULL,
  PRIMARY KEY (provider_id,category),
  CONSTRAINT fk_provider_category_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE
) ENGINE=InnoDB;
