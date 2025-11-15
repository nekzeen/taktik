#!/bin/bash

# Couleurs pour l'output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🎮 Configuration des Tournois de Test${NC}\n"

# Vérifier si on est dans le bon répertoire
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Erreur: artisan non trouvé. Assurez-vous d'être dans le répertoire racine du projet.${NC}"
    exit 1
fi

# Menu
echo -e "${YELLOW}Sélectionnez une option :${NC}"
echo "1) Réinitialiser la BD et créer tous les tournois (migrate:fresh --seed)"
echo "2) Créer uniquement les tournois de test (seed:test-tournaments)"
echo "3) Créer uniquement les tournois (db:seed --class=TournamentTestSeeder)"
echo "4) Afficher les utilisateurs créés"
echo "5) Afficher les tournois créés"
echo "6) Quitter"
echo ""

read -p "Votre choix (1-6) : " choice

case $choice in
    1)
        echo -e "\n${BLUE}🔄 Réinitialisation de la base de données...${NC}"
        php artisan migrate:fresh --seed
        echo -e "\n${GREEN}✅ Terminé !${NC}"
        ;;
    2)
        echo -e "\n${BLUE}🎮 Création des tournois de test...${NC}"
        php artisan seed:test-tournaments
        echo -e "\n${GREEN}✅ Terminé !${NC}"
        ;;
    3)
        echo -e "\n${BLUE}🎮 Création des tournois...${NC}"
        php artisan db:seed --class=TournamentTestSeeder
        echo -e "\n${GREEN}✅ Terminé !${NC}"
        ;;
    4)
        echo -e "\n${BLUE}👥 Utilisateurs créés :${NC}\n"
        php artisan tinker << 'EOF'
User::all()->each(function($user) {
    echo sprintf("  • %s (%s) - Rôles: %s\n", 
        $user->name, 
        $user->email,
        $user->getRoleNames()->join(', ')
    );
});
EOF
        ;;
    5)
        echo -e "\n${BLUE}🎮 Tournois créés :${NC}\n"
        php artisan tinker << 'EOF'
Tournament::with('creator')->get()->each(function($tournament) {
    $count = $tournament->armyLists()->count();
    echo sprintf("  • %s (créateur: %s) - %d inscrits\n", 
        $tournament->name, 
        $tournament->creator->name,
        $count
    );
});
EOF
        ;;
    6)
        echo -e "${YELLOW}Au revoir !${NC}"
        exit 0
        ;;
    *)
        echo -e "${RED}❌ Option invalide${NC}"
        exit 1
        ;;
esac

echo -e "\n${BLUE}📧 Identifiants de connexion :${NC}"
echo "  • nek@test.fr / password"
echo "  • nekzeen@test.fr / password"
echo "  • [prenom.nom]@test.fr / password"

echo -e "\n${BLUE}🔗 Accès aux tournois :${NC}"
echo "  • http://localhost/tournaments"

echo -e "\n${GREEN}✨ Configuration terminée !${NC}\n"
