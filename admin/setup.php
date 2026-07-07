<?php

require __DIR__ . '/../../../main.inc.php';
require_once DOL_DOCUMENT_ROOT . '/product/stock/class/entrepot.class.php';
dol_include_once('/lotvalorisation/class/lotvalorisation.class.php');

// global $db;

$langs->load(array('admin', 'stocks', 'lotvalorisation@lotvalorisation'));

if (!$user->admin) {
    accessforbidden();
}

$action = GETPOST('action', 'aZ09');
$warehouses = \LotValorisation::getWarehouseModes($db);

if ($action === 'save') {
    $db->begin();
    $error = 0;

    foreach ($warehouses as $warehouse) {
        $mode = GETPOST('mode_' . $warehouse['rowid'], 'aZ09');
        if (!in_array($mode, array(\LotValorisation::MODE_DEFAULT, \LotValorisation::MODE_LOT), true)) {
            $mode = \LotValorisation::MODE_DEFAULT;
        }
        if (\LotValorisation::setWarehouseMode($db, $warehouse['rowid'], $mode) < 0) {
            $error++;
        }
    }

    if ($error) {
        $db->rollback();
        setEventMessages($langs->trans('Error'), null, 'errors');
    } else {
        $db->commit();
        setEventMessages($langs->trans('SetupSaved'), null, 'mesgs');
    }
}

llxHeader('', $langs->trans('LotValorisation'));

print load_fiche_titre($langs->trans('LotValorisation'), '', 'stock');
print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="save">';

print '<div class="opacitymedium marginbottomonly">' . $langs->trans('LotValorisationDesc') . '</div>';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans('Warehouse') . '</td>';
print '<td>' . $langs->trans('LotValorisationMode') . '</td>';
print '</tr>';

foreach ($warehouses as $warehouse) {
    print '<tr class="oddeven">';
    print '<td>' . dol_escape_htmltag($warehouse['ref']) . ($warehouse['lieu'] ? ' - ' . dol_escape_htmltag($warehouse['lieu']) : '') . '</td>';
    print '<td><select name="mode_' . $warehouse['rowid'] . '">';
    print '<option value="' . \LotValorisation::MODE_DEFAULT . '"' . ($warehouse['mode'] === \LotValorisation::MODE_DEFAULT ? ' selected' : '') . '>' . $langs->trans('LotValorisationModeDefault') . '</option>';
    print '<option value="' . \LotValorisation::MODE_LOT . '"' . ($warehouse['mode'] === \LotValorisation::MODE_LOT ? ' selected' : '') . '>' . $langs->trans('LotValorisationModeLot') . '</option>';
    print '</select></td>';
    print '</tr>';
}

print '</table>';
print '<div class="center margin-top"><input type="submit" class="button button-save" value="' . $langs->trans('Save') . '">';
print '</div>';
print '</form>';

llxFooter();
