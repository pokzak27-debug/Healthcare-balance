# API REST — Healthcare Balance

**Devise :** Dirham marocain (DH) exclusivement. Les montants sont formatés : `1 500,00 DH`.

## Authentification

### Obtenir un token

```http
POST /api/token
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

Réponse :
```json
{
  "token": "1|xxxxx...",
  "type": "Bearer"
}
```

### Utiliser le token

Ajouter l'en-tête à toutes les requêtes :
```
Authorization: Bearer {token}
```

### Révoquer le token

```http
DELETE /api/token
Authorization: Bearer {token}
```

---

## Endpoints

Base URL : `/api/v1`

### Patients

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | /api/v1/patients | Liste paginée (params: search, per_page) |
| POST | /api/v1/patients | Créer un patient |
| GET | /api/v1/patients/{id} | Détail patient |
| PUT/PATCH | /api/v1/patients/{id} | Modifier |
| DELETE | /api/v1/patients/{id} | Supprimer |

### Rendez-vous

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | /api/v1/appointments | Liste paginée (params: pole_id, user_id, status, date_from, date_to) |
| POST | /api/v1/appointments | Créer un RDV |
| GET | /api/v1/appointments/{id} | Détail RDV |
| PUT/PATCH | /api/v1/appointments/{id} | Modifier |
| DELETE | /api/v1/appointments/{id} | Supprimer |

### Pôles

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | /api/v1/poles | Liste des pôles actifs |
| POST | /api/v1/poles | Créer un pôle |
| GET | /api/v1/poles/{id} | Détail pôle |
| PUT/PATCH | /api/v1/poles/{id} | Modifier |
| DELETE | /api/v1/poles/{id} | Supprimer |

---

## Exemples

### Créer un patient

```json
POST /api/v1/patients
{
  "first_name": "Jean",
  "last_name": "Dupont",
  "email": "jean@example.com",
  "phone": "0612345678",
  "birth_date": "1980-01-15",
  "sexe": "M"
}
```

### Créer un rendez-vous

```json
POST /api/v1/appointments
{
  "patient_id": 1,
  "pole_id": 1,
  "user_id": 2,
  "appointment_date": "2026-03-20",
  "appointment_time": "10:00",
  "duration_minutes": 30,
  "status": "scheduled",
  "reason": "Consultation"
}
```
