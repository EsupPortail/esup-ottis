const express = require('express');
const bodyParser = require('body-parser');
const { DeepL } = require('@deeplcom/deepl-node');
const fs = require('fs');
const path = require('path');

const app = express();
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());

// Charger les tokens depuis tokens.json
const tokensPath = "../tables/tokens.json"; //path.join(__dirname, 'tables', 'tokens.json');
let tokens;
try {
    tokens = JSON.parse(fs.readFileSync(tokensPath, 'utf8'));
} catch (error) {
    console.error("Erreur lors de la lecture de tokens.json:", error);
    process.exit(1);
}

// Configuration des traducteurs
const DEEPLAPITOKEN = tokens.DEEPLPROTOKENS ? tokens.DEEPLPROTOKENS[0] : null;
const DEEPLFREEAPITOKEN = tokens.DEEPLFREETOKENS ? tokens.DEEPLFREETOKENS[0] : null;

const DEEPLKNOWNLANG = ["ar","bg","zh","cs","da","en","fi","fr","de","gr","hu","nl","it","jp","lv","lt","po","pt","ro","ru","sk","sl","es","sv","tr","in","uk"];
const LIBRETRANSLATEKNOWNLANG = ["en","ar","az","zh","cs","nl","eo","fi","fr","de","el","hi","hu","id","ga","it","ja","ko","fa","pl","pt","ru","sk","es","sv","tr","uk","vi"];

// Middleware pour logger les requêtes
app.use((req, res, next) => {
    console.log(`${new Date().toISOString()} - ${req.method} ${req.url}`);
    next();
});

// Route pour la traduction
app.get('/Translate.php', async (req, res) => {
    try {
        const { texte, lang, langinput, ROOMID, lineid = 'NONE', whichtranslator = 'DEEPLPRO', forcelibre } = req.query;

        if (!texte || !lang || !langinput || !ROOMID) {
            return res.status(400).json({
                error: true,
                message: 'Paramètres manquants: texte, lang, langinput ou ROOMID.'
            });
        }

        let translatedText = '';
        const whichTranslator = forcelibre ? 'LIBRETRANSLATE' : whichtranslator;

        // Sélection du traducteur
        switch (whichTranslator) {
            case 'DEEPLPRO':
                if (!DEEPLAPITOKEN) {
                    return res.status(500).json({
                        error: true,
                        message: 'Clé API DeepL Pro manquante.'
                    });
                }
                try {
                    const deepl = new DeepL({ authKey: DEEPLAPITOKEN });
                    const result = await deepl.translateText(texte, langinput.toUpperCase(), lang.toUpperCase());
                    translatedText = result.text;
                } catch (error) {
                    console.error("Erreur DeepL Pro:", error.message);
                    if (error.message.includes("Quota exceeded")) {
                        return res.status(403).json({
                            error: true,
                            message: 'Quota DeepL Pro dépassé.'
                        });
                    } else if (error.message.includes("invalid auth_key")) {
                        return res.status(401).json({
                            error: true,
                            message: 'Clé API DeepL Pro invalide.'
                        });
                    } else {
                        return res.status(500).json({
                            error: true,
                            message: 'Erreur DeepL Pro: ' + error.message
                        });
                    }
                }
                break;

            case 'DEEPLFREE':
                // Implémenter la logique pour DeepL Free ici
                translatedText = "Traduction DeepL Free (à implémenter)";
                break;

            default:
                return res.status(400).json({
                    error: true,
                    message: 'Traducteur non supporté: ' + whichTranslator
                });
        }

        res.json({
            error: false,
            translator: whichTranslator,
            roomId: ROOMID,
            data: {
                translations: [
                    { translatedText: translatedText }
                ]
            }
        });

    } catch (error) {
        console.error("Erreur serveur:", error);
        res.status(500).json({
            error: true,
            message: 'Erreur interne du serveur: ' + error.message
        });
    }
});

// Démarrer le serveur
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Serveur de traduction démarré sur le port ${PORT}`);
});