# Credit2000 (Tourismo Filipino Checkout)

Hosted payment page via Credit2000 SOAP ASMX (`SendParamToCredit2000` → redirect → `getTokenAndApprove` → `CreditXML`).

## Env vars (do not commit secrets)

```env
CHECKOUT_CREDIT2000_ACTIVE=false
CHECKOUT_CREDIT2000_NAME=Credit2000
CHECKOUT_CREDIT2000_BASE_URL=https://www.credit2000.co.il/pci_emv_ver4/wcf/wscredit2000.asmx
CHECKOUT_CREDIT2000_VENDOR_NAME=
CHECKOUT_CREDIT2000_COMPANY_KEY=
CHECKOUT_CREDIT2000_LANG=he
CHECKOUT_CREDIT2000_PREPARE_ACTION_TYPE=5
CHECKOUT_CREDIT2000_PURCHASE_TYPE=1
```

Enable only on staging after credentials are configured:

```env
CHECKOUT_CREDIT2000_ACTIVE=true
```

## Lifecycle (Nezasa authorize → book → capture/abort)

| Package step | Credit2000 action | Success |
|--------------|-------------------|---------|
| `prepare()` | `SendParamToCredit2000` (`return_Code=123`) | hosted payment URL |
| `authorize()` | callback `params` (uid) + **`getTokenAndApprovePro(uid)` only** | live-verified Pro success checks (below) |
| `capture()` | `CreditXML` `actionType=4` (or no-op if page already charged) | `returnCode=000` |
| `abort()` | if charged: `CreditXML` `actionType=7` refund; if uncaptured ActionType `5`: **no provider call** — checkout marks abort successful and leaves the approval to expire | refund: `returnCode=000`; uncaptured: `cancel.mode=uncaptured_approval_left_to_expire` |

### `authorize()` — live-verified Pro rules (ActionType 5)

Authorization uses **`getTokenAndApprovePro` only**. Live testing showed `getTokenAndApprove` (non-Pro) returned empty fields/token even after a successful ActionType 5 page approval, so it is **not** used for the authorize decision.

Callback `params` UID is the server-to-server lookup key. **Pro `uID` is not required** (successful live responses left it empty).

All of the following must hold:

| Check | Success (live) | Failure baseline (live) |
|-------|----------------|-------------------------|
| `product_Id` matches prepare | match | match (echo) |
| `total_Pyment` matches prepare | match | match (echo) |
| `currency` matches prepare | match | match (echo) |
| `action_Type` matches prepare **and** is `5` | `5` | `5` (echo) |
| `return_Code` | **`000`** | `123` (SendParam echo) |
| `Approve` | non-empty and **not** `0000000` | `0000000` placeholder |
| `ValidDate` | MMYY (`/^\d{4}$/`) for capture | empty |
| `token` | present | absent |

### `prepare_action_type`

| Value | Meaning |
|-------|---------|
| `5` | Approval only (preferred). Capture charges later via CreditXML. |
| `4` | Charge on payment page. Capture is treated as already done. |
| `2` | SendParams Test mode — **rejected by checkout**. Not safe: capture would still call CreditXML `actionType=4` (charge). Use a Credit2000 test terminal with `5` or `4` instead. |

## Amounts

`total_Pyment` / `totalPayment` use **minor units** (agorot). Example: `₪101.00` → `10100`.

### SendParams `club`

SendParam serializes `<club>0</club>` (not an empty element). Live Credit2000 error **589** (“club can not have non numerical characters”) rejected `<club></club>`; their SOAP example also sends `club=0`.

## Portal / provider requirements

1. Use the merchant ASMX endpoint: `pci_emv_ver4` (confirmed by Credit2000; redirect path matches SendParam). Older `pci_tkn_ver7` is obsolete for this terminal.
2. Provide production `vendor_Name` and `company_Key`. **There is no separate sandbox** — this is a live terminal.
3. Confirm whether **approval-only (`5`)** is enabled for the terminal (needed for two-phase booking).
4. Confirm refund (`CreditXML` action `7`) works for aborted bookings after charge.
5. Ensure success redirect lands on the Checkout App result URL (passed as `host`).

## Live-verified notes

- Environment is **production**. For E2E, use a small real card amount; Credit2000 can cancel a charge the same day if notified.
- Uncaptured approvals (ActionType `5`) usually auto-expire within ~3 business days (depends on the merchant–acquirer agreement).
- Credit2000 **did not provide** an API to release/cancel an uncaptured ActionType `5` approval. Checkout `abort()` therefore does **not** claim a provider-side release; it records `uncaptured_approval_left_to_expire` and blocks later capture.
- Hosted `host` callback appends `params` (UID) with `&` when the return URL already has a query string (observed live).
- Error **93** = wrong action key (credentials / terminal config) — not a card decline code in Pro fields.
- Error **589** = non-numeric `club`; fixed by sending `<club>0</club>`.

## Still open / staging

- Installments (`purchase_Type=2`) — currently defaulted to regular (`1`)
- Full Checkout App E2E with a real staging `host` (not a placeholder domain)
- Capture (`CreditXML` 4) and refund (`CreditXML` 7) against the live terminal