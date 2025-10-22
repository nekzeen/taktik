# 📄 SYSTÈME D'UPLOAD ET D'ANALYSE DE LISTES D'ARMÉES PDF

## ✅ FONCTIONNALITÉS IMPLÉMENTÉES

Un système complet d'upload de PDF avec analyse automatique des listes d'armées Warhammer 40k a été implémenté.

---

## 🎯 FONCTIONNALITÉS

### **1. Upload de PDF** ✅
- Upload de fichiers PDF (max 5 Mo)
- Stockage sécurisé dans `storage/app/private/army-lists`
- Calcul automatique du hash SHA-256
- Enregistrement de la taille du fichier

### **2. Analyse Automatique** ✅
Le système analyse automatiquement le PDF et extrait :
- **Faction** : Détection automatique (Space Marines, Necrons, etc.)
- **Détachement** : Extraction du détachement utilisé
- **Points** : Total de points de la liste
- **Unités** : Liste des unités présentes (en développement)

### **3. Interface Admin Améliorée** ✅
- Formulaire organisé en sections
- Labels en français
- Badges colorés pour les statuts
- Actions de validation/rejet
- Téléchargement des PDFs

---

## 📋 FORMULAIRE DE CRÉATION

### **Section 1 : Informations générales**
- **Joueur** : Sélection du joueur
- **Tournoi** : Sélection du tournoi (optionnel)
- **Points** : Nombre de points (défaut: 2000)

### **Section 2 : Liste d'armée (PDF)**
- **Upload PDF** : Glisser-déposer ou sélectionner
- Format accepté : PDF uniquement
- Taille max : 5 Mo
- Analyse automatique après upload

### **Section 3 : Détails de l'armée**
- **Faction** : Rempli automatiquement
- **Détachement** : Rempli automatiquement
- Modification manuelle possible

### **Section 4 : Validation**
- **Statut** : Brouillon, En attente, Validée, Rejetée
- **Validée le** : Date de validation (auto)
- **Validée par** : Validateur (auto)
- **Raison du rejet** : Si rejetée

---

## 🤖 ANALYSE AUTOMATIQUE

### **Service ArmyListAnalyzer**

**Fichier** : `app/Services/ArmyListAnalyzer.php`

#### **Méthodes principales**

##### **1. analyze(string $pdfPath): array**
Analyse complète du PDF

**Retourne** :
```php
[
    'faction_id' => 1,
    'faction_name' => 'Space Marines',
    'detachment' => 'Gladius Task Force',
    'points' => 2000,
    'units' => [...],
    'raw_text' => '...',
]
```

##### **2. detectFaction(string $text): ?Faction**
Détecte la faction depuis le texte

**Patterns recherchés** :
- Noms exacts des factions en base
- Mots-clés alternatifs (Aeldari, Craftworld, etc.)
- Noms anglais et français

##### **3. detectDetachment(string $text): ?string**
Détecte le détachement

**Patterns** :
- "Detachment: XXX"
- "Army Rule: XXX"
- Liste de détachements connus

##### **4. detectPoints(string $text): ?int**
Extrait le total de points

**Patterns** :
- "Total: XXX pts"
- "XXX points"
- "Army Total: XXX"

##### **5. extractUnits(string $text): array**
Extrait les unités (en développement)

**Format** :
```php
[
    ['quantity' => 10, 'name' => 'Intercessor Squad', 'points' => 150],
    ...
]
```

---

## 🎨 INTERFACE TABLEAU

### **Colonnes**
- **Joueur** : Nom du joueur
- **Tournoi** : Nom du tournoi
- **Faction** : Badge bleu
- **Détachement** : Texte limité à 30 caractères
- **Points** : Nombre
- **Statut** : Badge coloré
  - 🟤 Brouillon (gris)
  - 🟠 En attente (orange)
  - 🟢 Validée (vert)
  - 🔴 Rejetée (rouge)
- **PDF** : Icône ✓ ou ✗

### **Filtres**
- **Par statut** : Brouillon, En attente, Validée, Rejetée
- **Par faction** : Toutes les factions

### **Actions**
- **Valider** : Valide une liste en attente
- **Rejeter** : Rejette avec raison
- **Modifier** : Éditer la liste
- **Télécharger PDF** : Télécharge le PDF

---

## 🔄 WORKFLOW

### **1. Création d'une liste**
```
Admin crée une liste
↓
Upload du PDF
↓
Analyse automatique
↓
Faction et détachement remplis
↓
Validation manuelle possible
↓
Sauvegarde
```

### **2. Validation**
```
Liste en statut "En attente"
↓
Admin clique sur "Valider"
↓
Confirmation
↓
Statut → "Validée"
↓
Date et validateur enregistrés
```

### **3. Rejet**
```
Liste en statut "En attente"
↓
Admin clique sur "Rejeter"
↓
Saisie de la raison
↓
Statut → "Rejetée"
↓
Raison enregistrée
```

---

## 🔧 CONFIGURATION

### **Stockage**
**Fichier** : `config/filesystems.php`

```php
'private' => [
    'driver' => 'local',
    'root' => storage_path('app/private'),
    'serve' => true,
],
```

**Dossier** : `storage/app/private/army-lists/`

### **Permissions**
```bash
chmod -R 775 storage/app/private
chown -R web12:client2 storage/app/private
```

---

## 📦 DÉPENDANCES

### **Bibliothèque PDF**
```bash
composer require smalot/pdfparser
```

**Package** : `smalot/pdfparser` v2.12.1

**Fonctionnalités** :
- Extraction de texte depuis PDF
- Support des PDFs complexes
- Pas de dépendances système

---

## 🚀 UTILISATION

### **Créer une liste d'armée**
1. Aller sur **Listes d'armées** > **Créer**
2. Sélectionner le **joueur**
3. Sélectionner le **tournoi** (optionnel)
4. **Uploader le PDF** de la liste
5. Vérifier les informations détectées
6. Ajuster si nécessaire
7. **Enregistrer**

### **Valider une liste**
1. Aller sur **Listes d'armées**
2. Filtrer par statut **En attente**
3. Cliquer sur **Valider** (icône ✓)
4. Confirmer

### **Rejeter une liste**
1. Aller sur **Listes d'armées**
2. Cliquer sur **Rejeter** (icône ✗)
3. Saisir la **raison du rejet**
4. Confirmer

---

## 🎯 DÉTECTION DES FACTIONS

### **Factions supportées**
- Space Marines / Adeptus Astartes
- Necrons
- Orks / Greenskins
- Tyranids / Hive Fleet
- Chaos Space Marines / Heretic Astartes
- Astra Militarum / Imperial Guard
- T'au Empire
- Aeldari / Craftworld / Eldar
- Drukhari / Dark Eldar
- Adeptus Mechanicus

### **Amélioration future**
- Intégration avec BSData (GitHub)
- Détection des sous-factions
- Extraction des stratagèmes
- Validation des points

---

## 📊 DONNÉES EXTRAITES

### **Métadonnées du PDF**
- **pdf_path** : Chemin du fichier
- **pdf_size** : Taille en octets
- **pdf_hash** : Hash SHA-256 (unicité)

### **Données de la liste**
- **faction_id** : ID de la faction
- **detachment** : Nom du détachement
- **points** : Total de points
- **status** : Statut de validation

### **Données de validation**
- **validated_at** : Date de validation
- **validated_by** : ID du validateur
- **rejection_reason** : Raison du rejet

---

## 🔮 AMÉLIORATIONS FUTURES

### **Phase 1 : Intégration BSData** 🔄
- Télécharger les données depuis GitHub
- Parser les fichiers XML/JSON
- Créer une base de données locale
- Valider les unités et équipements

### **Phase 2 : Validation avancée** 🔄
- Vérifier la légalité de la liste
- Valider les points
- Détecter les erreurs de composition
- Suggérer des corrections

### **Phase 3 : Interface joueur** 🔄
- Permettre aux joueurs d'uploader leurs listes
- Notification de validation/rejet
- Historique des listes
- Statistiques

### **Phase 4 : Analyse IA** 🔄
- OCR pour PDFs scannés
- Détection d'images
- Extraction de tableaux
- Analyse sémantique

---

## 🐛 DÉPANNAGE

### **PDF non analysé**
**Problème** : Le PDF est uploadé mais pas analysé

**Solutions** :
1. Vérifier les permissions du dossier `storage/app/private`
2. Vérifier les logs : `storage/logs/laravel.log`
3. Tester manuellement l'extraction :
```php
$analyzer = new \App\Services\ArmyListAnalyzer();
$result = $analyzer->analyze('army-lists/test.pdf');
dd($result);
```

### **Faction non détectée**
**Problème** : La faction n'est pas détectée automatiquement

**Solutions** :
1. Vérifier que la faction existe en base de données
2. Ajouter des patterns dans `detectFaction()`
3. Saisir manuellement la faction

### **Erreur d'upload**
**Problème** : Erreur lors de l'upload du PDF

**Solutions** :
1. Vérifier la taille du fichier (max 5 Mo)
2. Vérifier le format (PDF uniquement)
3. Vérifier `upload_max_filesize` dans `php.ini`

---

## ✅ RÉSULTAT

**Système complet d'upload et d'analyse de listes d'armées !**

- ✅ Upload de PDF sécurisé
- ✅ Analyse automatique
- ✅ Détection de faction et détachement
- ✅ Interface admin complète
- ✅ Actions de validation/rejet
- ✅ Filtres et recherche
- ✅ Téléchargement des PDFs

**Les listes d'armées peuvent maintenant être gérées efficacement ! 📄**

---

**Date** : 21 octobre 2025, 01h12
**Fichiers créés** : 2
**Fichiers modifiés** : 3
**Package installé** : smalot/pdfparser
**Status** : ✅ Fonctionnel
