<?php

namespace App\Data;

/**
 * Dictionnaire de terminologie Warhammer 40k
 * Utilisé pour améliorer les traductions automatiques
 */
class Wh40kTerminology
{
    /**
     * Termes spécifiques à Warhammer 40k avec leurs traductions correctes
     */
    public static function getTerminology(): array
    {
        return [
            // Génériques
            'Lords of Dread' => 'Seigneurs de l\'effroi',
            'Dread' => 'Effroi',
            'Chaos' => 'Chaos',
            'Emperor' => 'Empereur',
            'Imperium' => 'Imperium',
            
            // Factions
            'Space Marines' => 'Space Marines',
            'Chaos Space Marines' => 'Space Marines du Chaos',
            'Adeptus Mechanicus' => 'Adeptus Mechanicus',
            'Astra Militarum' => 'Astra Militarum',
            'Adepta Sororitas' => 'Adepta Sororitas',
            'Adeptus Custodes' => 'Adeptus Custodes',
            'Grey Knights' => 'Chevaliers Gris',
            'Necrons' => 'Nécrons',
            'Orks' => 'Orks',
            'Tyranids' => 'Tyranides',
            'Aeldari' => 'Aeldari',
            'Drukhari' => 'Drukhari',
            'T\'au Empire' => 'Empire T\'au',
            'Genestealer Cults' => 'Cultes Génévoleurs',
            'Chaos Knights' => 'Chevaliers du Chaos',
            'Chaos Daemons' => 'Démons du Chaos',
            'Imperial Knights' => 'Chevaliers Impériaux',
            'Leagues of Votann' => 'Ligues de Votann',
            
            // Unités communes
            'Shield Host' => 'Hôte du Bouclier',
            'Stratagem' => 'Stratagème',
            'Detachment' => 'Détachement',
            'Ability' => 'Capacité',
            'Datasheet' => 'Feuille de Données',
            
            // Termes militaires
            'Ambush' => 'Embuscade',
            'Perfect Ambush' => 'Embuscade Parfaite',
            'Assault' => 'Assaut',
            'Charge' => 'Charge',
            'Stratagem' => 'Stratagème',
            'Command Point' => 'Point de Commandement',
            'Battle Round' => 'Tour de Bataille',
            
            // Caractéristiques
            'Strength' => 'Force',
            'Toughness' => 'Robustesse',
            'Armour' => 'Armure',
            'Invulnerable' => 'Invulnérable',
            'Morale' => 'Moral',
            'Leadership' => 'Commandement',
            
            // Armes
            'Bolter' => 'Bolter',
            'Plasma' => 'Plasma',
            'Melta' => 'Melta',
            'Flamer' => 'Lance-flammes',
            'Sword' => 'Épée',
            'Hammer' => 'Marteau',
            'Axe' => 'Hache',
        ];
    }

    /**
     * Obtenir la traduction personnalisée d'un terme
     */
    public static function translate(string $term): ?string
    {
        $terminology = self::getTerminology();
        return $terminology[$term] ?? null;
    }

    /**
     * Vérifier si un terme a une traduction personnalisée
     */
    public static function has(string $term): bool
    {
        return isset(self::getTerminology()[$term]);
    }
}
