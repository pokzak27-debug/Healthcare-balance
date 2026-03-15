# Guide utilisateur — Healthcare Balance

**Devise :** Dirham marocain (DH) exclusivement.

Application de gestion pour cabinet de santé (ostéopathie, kinésithérapie, esthétique médicale, massage médical).

## Rôles

- **Super Admin** : gestion pôles, utilisateurs, créneaux, logs
- **Praticien (doctor)** : accès patients, rendez-vous
- **Assistante (staff)** : accès patients, rendez-vous

## Fonctionnalités

### Patients
- Liste, création, modification, suppression
- Fiche patient avec historique des rendez-vous
- Export PDF de la fiche patient

### Rendez-vous
- Liste filtrée (pôle, statut, dates, praticien)
- Calendrier mensuel
- Création/édition avec durée (15, 30, 45, 60, 90 min)
- Statuts : Prévu, Honoré, Annulé, Absent
- Export CSV et PDF
- Rappels email automatiques (veille du RDV à 18h)

### Créneaux de disponibilité
- Définition des plages horaires par praticien ou pôle
- Par jour de la semaine (Lundi–Dimanche)
- Gérés par le Super Admin

### Recherche globale
- Un seul champ pour chercher dans patients et rendez-vous
- Résultats patients + rendez-vous

### Logs d'activité
- Historique des actions (création, modification, suppression)
- Accessible uniquement au Super Admin

### Dashboard
- Statistiques (patients, RDV, RDV du jour)
- Graphique 7 derniers jours
- Répartition par statut, pôle, praticien

## API REST

Voir [docs/API.md](API.md) pour l’authentification et les endpoints.
