<?php

return array (
  'help' => 
  array (
    'removed_email_log' => 'Adresse électronique retirée :address',
    'send_error' => 'Impossible de transférer le message à :email: :message',
  ),
  'summary' => 
  array (
    'defaults' => 
    array (
      'subject' => 'Résumé des réservations du GAS : :supplier - consegna :delivery',
    ),
  ),
  'closed' => 
  array (
    'defaults' => 
    array (
      'subject' => 'Commande fermée automatiquement',
    ),
  ),
  'notification' => 
  array (
    'defaults' => 
    array (
      'subject' => 'Nouvelle notification de :gas',
    ),
  ),
  'new_user_notification' => 
  array (
    'defaults' => 
    array (
      'subject' => 'Nouvel utilisateur enregistré',
    ),
  ),
  'contacts_prefix' => '',
  'approved' => 
  array (
    'defaults' => 
    array (
      'subject' => 'Bienvenue !',
      'body' => 'Bienvenue dans %[gas_name] !
À l\'avenir, vous pourrez vous connecter en utilisant le lien ci-dessous, l\'identifiant « %[username] » et le mot de passe de ton choix.
%[gas_login_link]
Un courriel de notification a été envoyé aux administrateurs.',
    ),
    'description' => '',
    'username' => 'Identifiant assigné au nouvel utilisateur',
    'link' => 'Lien vers la page de connexion',
  ),
  'declined' => 
  array (
    'defaults' => 
    array (
      'subject' => '',
      'body' => '',
    ),
    'description' => '',
  ),
  'mail' => 
  array (
    'welcome' => 
    array (
      'defaults' => 
      array (
        'body' => 'Vous avez été invité·e à %[gas_name] !

Pour vous connecter la première fois, cliquez sur le lien ci-dessous.
%[gas_access_link].

À l\'avenir, vous pourrez vous connecter en utilisant cet autre lien, le nom d\'utilisateur « %[username] » et le mot de passe que vous avez choisi.
%[gas_login_link]
',
      ),
    ),
  ),
  'order' => 
  array (
    'defaults' => 
    array (
      'subject' => 'Nouvelle commande ouverte pour %[supplier_name]',
      'body' => 'Une nouvelle commande a été ouverte sur %[gas_name] pour le fournisseur %[supplier_name].
Pour participer, connectez-vous via ce lien :
%[gas_booking_link]
Les réservations seront fermées le %[closing_date]',
    ),
    'description' => 'Notification pour les nouvelles commandes ouvertes (envoyée aux utilisateurs qui ont activé explicitement les notifications pour le fournisseur).',
    'comment' => 'Texte commenté de la commande',
    'link' => 'Lien vers la réservation',
    'mails' => 'Adresses courriel des personnes de contact de la commande',
  ),
  'reminder' => 
  array (
    'defaults' => 
    array (
      'subject' => '',
      'body' => '',
    ),
    'description' => '',
    'list' => '',
  ),
  'password' => 
  array (
    'defaults' => 
    array (
      'body' => 'La mise à jour de votre mot de passe a été demandée sur GASdotto.
Cliquez sur le lien ci-dessous pour mettre à jour votre mot de passe ou ignorer ce courriel si vous n\'avez pas demandé cette opération.
%[gas_reset_link]',
    ),
    'description' => 'Message pour la récupération du mot de passe.',
    'link' => 'Lien pour la réinitialisation du mot de passe',
  ),
  'receipt' => 
  array (
    'defaults' => 
    array (
      'subject' => 'Nouvelle facture de %[gas_name]',
      'body' => 'Ci-joint la dernière facture de% [gas_name]',
    ),
    'description' => 'Courriel d\'accompagnement pour les reçus.',
  ),
  'supplier' => 
  array (
    'defaults' => 
    array (
      'subject' => 'Réservation de la commande %[gas_name]',
      'body' => 'Bonjour.
Veuillez trouver ci-joint – en double, PDF et CSV – la réservation de la commande de %[gas_name].
Si vous avez des questions, veuillez contacter les personnes de contact en copie de ce courriel.
Merci.',
    ),
    'description' => 'Notification aux fournisseurs de la fermeture automatique des commandes.',
  ),
  'credit' => 
  array (
    'current' => '',
  ),
  'welcome' => 
  array (
    'description' => 'Message envoyé aux nouveaux utilisateurs créés sur la plateforme.',
    'link' => 'Lien pour se connecter la première fois',
  ),
  'newuser' => 
  array (
    'description_manual' => '',
    'description' => 'Message envoyé aux nouveaux membres inscrits sur la plateforme.',
  ),
);