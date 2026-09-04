USE namvoy;

INSERT INTO destinations (name,country) VALUES
('Da Nang','Vietnam'),
('Hoi An','Vietnam'),
('Hanoi','Vietnam'),
('Ha Long Bay','Vietnam'),
('Ho Chi Minh City','Vietnam'),
('Phu Quoc','Vietnam')
ON DUPLICATE KEY UPDATE status='active';
