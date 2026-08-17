# Automatic Translator for Teaching purposes

Automatic Translator for Teaching purposes est un outil de traduction instantanée à usage pédagogique.

Ses fonctionnalités principales sont :
- la reconnaissance de la parole de l'enseignant
- la traduction du discours dans une centaine de langues
- la synthèse du discours dans la langue de l'étudiant
- un outil de chat multilingue pour simplifier les intéractions entre l'enseignant et les étudiants
- la prise de note automatique multilingue
- la diffusion de présentation traduites

Des outils compagnons, les OMIST_Tools permettent de travailler à partir de divers types de présentations (Powerpoint et LaTeX/Beamer par exemple).

Les pages principales sont : 
- l'accueil (les scénarios) index.php
- l'administration Admin/Administration.php (accessible seulement pour des utilisateurs administrateurs)
- la gestion des comptes externes Admin/Registration/admin/home.php (accessible seulement pour des utilisateurs administrateurs)
- le tableau de bord Admin/Stats/Dashboard.php (accessible seulement pour des utilisateurs administrateurs)

# Contributeurs

Jérémie Bourdon
Faculté des Sciences et Techniques
Nantes Université

Jeanne Dionisi
Projet OMIST
Nantes Université

Version 3.2.0

# Installation

#### 1. Copier le dépôt Git en local ou sur une machine distante


`git clone https://gitlab.univ-nantes.fr/bourdon-j/omist-automatic-translator-public.git`

ou

`git@gitlab.univ-nantes.fr:bourdon-j/omist-automatic-translator-public.git`

#### 2. Mettre en place les bases de données
[Pour les OMIST Tools](https://gitlab.univ-nantes.fr/bourdon-j/omist_automatictranslator/-/blob/master/OMIST_Tools/README.md?ref_type=heads)

#### 3. La version actuelle vient sans clés de traduction

Pour activer les options de traduction, il convient d'éditer le fichier tables/tokens.json pour y ajouter vos propres identifiants.
