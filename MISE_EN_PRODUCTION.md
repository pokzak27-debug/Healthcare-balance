# HB Smart Clinic – Mise en production

## Sécurité et verrouillage mots de passe

- **Super Admin** : seul rôle pouvant définir ou modifier les mots de passe (création d’utilisateurs, édition, formulaire « Mot de passe » dans Profil).
- **Utilisateurs standards** (Praticien, Assistante) : pas d’accès au changement de mot de passe, pas de « Mot de passe oublié ».
- Routes **forgot-password** et **reset-password** sont désactivées dans `routes/auth.php`.
- Le lien « Mot de passe oublié » a été retiré de la page de connexion.
- L’URL `/settings/password` renvoie **403** pour tout utilisateur non super_admin.
- La modification des utilisateurs (CRUD) est protégée par le middleware `role:super_admin` (`routes/web.php`).

## Nettoyage des données (base propre)

- **Commande** : `php artisan data:wipe [--force]`  
  Vide les tables : `patients`, `appointments`, `quotes`, `invoices`, `medical_entries`, `diagnostic_fishes`, `doctor_patient_consignes`, `patient_schedule_entries`, `patient_schedule_items`, et met à `null` les références optionnelles (`staff_instructions`, `ai_assistant_logs`, `rooms`). La structure des tables est conservée.

## Base prête pour la production

1. **Reset complet** (à lancer manuellement) :  
   `php artisan migrate:fresh`
2. **Seeders production** (rôles, un Super Admin, catalogue SPECTA MedCryo PRO / EMSculpt) :  
   `php artisan db:seed`
3. **Stabilité / caches** :  
   `php artisan optimize`

Le `DatabaseSeeder` appelle : `RoleSeeder`, `PoleSeeder`, `SuperAdminOnlySeeder`, `SpectaCatalogSeeder`.  
Compte Super Admin par défaut : `admin@healthcare.local`. Mot de passe : variable d’environnement `APP_SUPER_ADMIN_PASSWORD` ou, à défaut, `password` (à changer en production).

## Vérifications finales

1. **Accès unique** : connecté en tant que Praticien, ouvrir `/settings/password` → **403 Forbidden**.
2. **Zéro donnée** : après `data:wipe` ou après `migrate:fresh` + `db:seed`, la table `patients` doit être à **0** ligne (hors seed démo si vous en exécutez un).
3. **Stabilité** : exécuter `php artisan optimize` après déploiement.
