# KasaRazem

System zarządzania wydatkami grupowymi z funkcjami logowania audytowego i obsługą wielu języków.

## Funkcje

- System logowania i rejestracji
- Zarządzanie grupami wydatków
- Śledzenie wydatków i rozliczeń
- Listy zakupów
- Audyt logowania (bez przechowywania haseł)
- Obsługa wielu języków (polski, angielski)

## Wymagania

- Docker
- Docker Compose

## Instalacja

```bash
docker-compose up -d
```

## Multijęzykowość

System automatycznie wykrywa język przeglądarki użytkownika. Obsługiwane języki:
- Polski (domyślny)
- Angielski

Zmiana języka: `/change-language?lang=en` lub `/change-language?lang=pl`

## Audyt bezpieczeństwa

Wszystkie próby logowania (udane i nieudane) są zapisywane w tabeli `audit_logs` bez haseł użytkowników.

Typy zdarzeń:
- `login_success` - udane logowanie
- `login_failed` - nieudane logowanie

Zapytanie SQL do przeglądania nieudanych prób:
```sql
SELECT * FROM audit_logs 
WHERE event_type = 'login_failed' 
AND created_at > NOW() - INTERVAL '24 hours'
ORDER BY created_at DESC;
```
