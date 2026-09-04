# NamVoy MVP v0.1

The first milestone deliberately proves the marketplace loop before payment:

**Register → Login → Create Travel Request → Provider receives RFQ → Provider submits bid → Traveler compares bids**

This matches the product specification's first working milestone. Payment, booking confirmation, reviews, AI, inventory and instant booking are later phases.

## Deploy prerequisites

- PHP 8.x with mysqli enabled
- MySQL 8.x or compatible MySQL server
- Apache with `.htaccess` support
- HTTPS in production

## Installation

1. Create a MySQL database/user.
2. Import `database/schema.sql`.
3. Set the database constants in `config/config.php` (prefer environment-backed configuration before production).
4. Point the web server document root at the repository root.
5. Create one traveler account and one provider account using `/register.php`.
6. For local testing, mark the provider as verified in MySQL:

```sql
UPDATE providers SET verification_status = 'verified' WHERE user_id = <provider_user_id>;
```

7. Login as traveler, create an RFQ, then login as the verified provider and submit an offer.
8. Return to the traveler request page to compare offers.

## Security baseline

- Passwords use `password_hash()` / `password_verify()`.
- Database writes use MySQLi prepared statements.
- Forms use session CSRF tokens.
- Output is HTML-escaped.
- Role checks protect traveler/provider areas.
- No production credentials should be committed to Git.

## Next implementation slice

1. Provider verification/admin console.
2. Provider matching and NamVoy Score.
3. Offer line items and match scoring.
4. In-platform messaging.
5. Offer acceptance and booking creation.
6. Payment gateway integration after booking flow is proven.
7. AI trip planner using OpenAI API with server-side validation; AI remains an intelligence layer and MySQL remains the source of truth.
