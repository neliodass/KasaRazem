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

1. Skopiuj plik przykładowej konfiguracji:
```bash
cp .env.example .env
```

2. Edytuj plik `.env` i dostosuj konfigurację do swoich potrzeb:
```bash
nano .env
```

3. Uruchom kontenery Docker:
```bash
docker-compose up -d
```

4. Aplikacja będzie dostępna pod adresem: `http://localhost:8080`

## Konfiguracja

Wszystkie ustawienia znajdują się w pliku `.env`. Najważniejsze zmienne:

### Baza danych
- `POSTGRES_DB` - nazwa bazy danych (domyślnie: `db`)
- `POSTGRES_USER` - użytkownik bazy danych (domyślnie: `docker`)
- `POSTGRES_PASSWORD` - hasło do bazy danych (domyślnie: `docker`)
- `POSTGRES_PORT` - port bazy danych (domyślnie: `5432`)

### pgAdmin
- `PGADMIN_DEFAULT_EMAIL` - email do logowania do pgAdmin (domyślnie: `admin@example.com`)
- `PGADMIN_DEFAULT_PASSWORD` - hasło do pgAdmin (domyślnie: `admin`)
- `PGADMIN_PORT` - port pgAdmin (domyślnie: `5050`)

Dostęp do pgAdmin: `http://localhost:5050`

### Tryb demo
- `DEMO_MODE` - włącza/wyłącza tryb demo z przykładowymi danymi (domyślnie: `true`)

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

## Zarządzanie kontenerami

Uruchomienie:
```bash
docker-compose up -d
```

Zatrzymanie:
```bash
docker-compose down
```

Zatrzymanie i usunięcie wolumenów (dane zostaną usunięte!):
```bash
docker-compose down -v
```

Przebudowa kontenerów po zmianach:
```bash
docker-compose up -d --build
```

Logi:
```bash
docker-compose logs -f
```
