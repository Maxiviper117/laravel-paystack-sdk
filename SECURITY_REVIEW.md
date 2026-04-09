# Security Review

Scope: `maxiviper117/laravel-paystack-sdk`

## Summary

I reviewed the security-sensitive parts of the package with emphasis on webhook intake, signature validation, persisted billing state, and public SDK request paths.

No confirmed vulnerabilities were found in the reviewed code paths.

## Findings

### No confirmed findings

The webhook flow validates signatures before storage, stores the raw payload for later processing, and dispatches processing only after validation. The Billable layer delegates through the package manager and typed DTOs rather than bypassing the normal transport layer.

## Hardening Notes

The public fetch-by-identifier request classes now encode the path fragment before joining it to the connector base URL:

- `src/Integrations/Requests/Customer/FetchCustomerRequest.php`
- `src/Integrations/Requests/Plan/FetchPlanRequest.php`
- `src/Integrations/Requests/Subscription/FetchSubscriptionRequest.php`

This closes the path-construction gap identified in the earlier review and prevents untrusted identifier values from altering the intended upstream path shape.

## Verification Performed

I ran the webhook-focused test subset:

- `tests/Feature/WebhookVerificationTest.php`
- `tests/Unit/PaystackSignatureValidatorTest.php`
- `tests/Unit/TypedWebhookDataTest.php`

Result: 20 tests passed, 54 assertions.

## Notes

This report reflects the code as reviewed during the session. If the SDK surface changes, especially around webhook handling, request path construction, or billable persistence, rerun the security pass.
