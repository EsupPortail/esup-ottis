#!/bin/bash
#
# check_ottis.sh - Sonde NRPE de supervision OTTIS
#
# Utilisation :
#   ./scripts/check_ottis.sh [URL]
#   URL : 1er argument ou variable CHECK_OTTIS_URL, defaut http://localhost/health
#
# Integration NRPE (command dans nrpe.cfg, chemin absolu requis) :
#   command[check_ottis]=/srv/ottis/scripts/check_ottis.sh
#
# Codes de retour (standard NRPE/Nagios/Centreon) :
#   0 OK       - service fonctionnel (status "ok")
#   1 WARNING  - service "degraded" (dependances optionnelles absentes)
#   2 CRITICAL - echec d'un check critique, reponse invalide ou service injoignable
#
# La route /health ne fait aucun appel externe (CAS, traduction, LibreOffice)
# et n'expose aucun secret : elle peut etre interrogee regulierement.

CHECK_OTTIS_URL="${1:-${CHECK_OTTIS_URL:-http://localhost/health}}"

BODY=$(curl -s --max-time 10 -w '\n%{http_code}' "$CHECK_OTTIS_URL" 2>/dev/null)
if [ -z "$BODY" ]; then
    echo "CRITICAL - OTTIS injoignable ($CHECK_OTTIS_URL)"
    exit 2
fi

HTTP_CODE="${BODY##*$'\n'}"
JSON="${BODY%$'\n'*}"

if [ "$HTTP_CODE" = "000" ]; then
    echo "CRITICAL - OTTIS injoignable ($CHECK_OTTIS_URL)"
    exit 2
fi

if [ "$HTTP_CODE" != "200" ]; then
    echo "CRITICAL - OTTIS repond $HTTP_CODE sur $CHECK_OTTIS_URL"
    exit 2
fi

STATUS=$(printf '%s' "$JSON" | grep -o '"status":"[^"]*"' | head -n 1 | cut -d'"' -f4)

case "$STATUS" in
    ok)
        echo "OK - OTTIS status ok"
        exit 0
        ;;
    degraded)
        DETAIL=$(printf '%s' "$JSON" | grep -o '"status":"warn","detail":"[^"]*"' | head -n 1 | cut -d'"' -f8)
        echo "WARNING - OTTIS degrade${DETAIL:+ : $DETAIL}"
        exit 1
        ;;
    *)
        echo "CRITICAL - OTTIS status invalide ($STATUS)"
        exit 2
        ;;
esac
