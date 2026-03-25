<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=1216, initial-scale=0.6">
    <meta name="color-scheme" content="dark">
    <meta name="referrer" content="no-referrer">
    <title><?= ($PAGE_TITLE) ?></title>
    <link rel="icon" href="<?= ($BASE) ?>/ui/images/favicon.png">
    <link rel="stylesheet" type="text/css" href="<?= ($BASE) ?>/ui/css/admin.all.css?dc=<?= (time()) ?>" />
    <?php if ((isset($EXTRA_CSS) && $EXTRA_CSS)): ?>
        <link rel="stylesheet" type="text/css" href="<?= ($BASE) ?><?= ($EXTRA_CSS) ?>?dc=<?= (time()) ?>" />
    <?php endif; ?>

    <?php if ((isset($LOAD_DATATABLE) && $LOAD_DATATABLE) || (isset($LOAD_JVECTORMAP) && $LOAD_JVECTORMAP) || (isset($LOAD_AUTOCOMPLETE) && $LOAD_AUTOCOMPLETE)): ?>
        <script type="text/javascript" src="<?= ($BASE) ?>/ui/js/vendor/jquery-3.6.0/jquery.min.js"></script>
    <?php endif; ?>

    <?php if (isset($LOAD_DATATABLE) && $LOAD_DATATABLE): ?>
        <script type="text/javascript" src="<?= ($BASE) ?>/ui/js/vendor/datatables-2.3.2/dataTables.min.js"></script>
        <script type="text/javascript" src="<?= ($BASE) ?>/ui/js/vendor/tooltipster-master-4.2.8/dist/js/tooltipster.bundle.min.js"></script>
    <?php endif; ?>

    <?php if (isset($LOAD_JVECTORMAP) && $LOAD_JVECTORMAP): ?>
        
        <script type="text/javascript" src="<?= ($BASE) ?>/ui/js/vendor/jvectormap-2.0.5/jquery-jvectormap-2.0.5.min.js?v=4"></script>
        
        <script src="<?= ($BASE) ?>/ui/js/vendor/jvectormap-2.0.5/jquery-jvectormap-world-mill-en.js?v=4"></script>
    <?php endif; ?>

    <?php if (isset($LOAD_UPLOT) && $LOAD_UPLOT): ?>
        <script type="text/javascript" src="<?= ($BASE) ?>/ui/js/vendor/uPlot-1.6.18/uPlot.iife.min.js"></script>
    <?php endif; ?>

    <?php if (isset($LOAD_ACCEPT_LANGUAGE_PARSER) && $LOAD_ACCEPT_LANGUAGE_PARSER): ?>
        <script type="text/javascript" src="<?= ($BASE) ?>/ui/js/vendor/accept-language-parser-1.5.0/index.js"></script>
    <?php endif; ?>

    <?php if (isset($LOAD_AUTOCOMPLETE) && $LOAD_AUTOCOMPLETE): ?>
        <script type="text/javascript" src="<?= ($BASE) ?>/ui/js/vendor/devbridge-jquery-autocomplete-1.5.0/jquery.autocomplete.min.js"></script>
    <?php endif; ?>

    <?php if (isset($LOAD_CHOICES) && $LOAD_CHOICES): ?>
        <script src="<?= ($BASE) ?>/ui/js/vendor/choices-10.2.0/choices.min.js"></script>
    <?php endif; ?>

    <?php if (isset($JS)): ?>
        
            <script>window.app_base = "<?= ($BASE) ?>"</script>
            <script type="module" src="<?= ($BASE) ?>/ui/js/endpoints/<?= ($JS) ?>?dc=<?= (time()) ?>"></script>
        
    <?php endif; ?>

    <?php if (isset($LOAD_EMAILJS) && $LOAD_EMAILJS): ?>
        
            <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
            <?php $pk = \Wolf\Utils\Variables::getEmailJsPublicKey();
                $sid = \Wolf\Utils\Variables::getEmailJsServiceId();
                $tid = \Wolf\Utils\Variables::getEmailJsTemplateId();
                $ej = [];
                if ($pk !== '') {
                    $ej['publicKey'] = $pk;
                }
                if ($sid !== '') {
                    $ej['serviceId'] = $sid;
                }
                if ($tid !== '') {
                    $ej['templateId'] = $tid;
                }
                if ($ej !== []) {
                    echo '<script>window.EMAILJS_CONFIG = Object.assign(window.EMAILJS_CONFIG || {}, ' . json_encode($ej, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . ');</script>';
                } ?>
            <script src="<?= ($BASE) ?>/ui/js/emailjs_integration.js?dc=<?= (time()) ?>"></script>
        
    <?php endif; ?>

    <meta name="format-detection" content="telephone=no">
    <meta name="google" content="notranslate">
    <meta name="generator" content="FrontPage 4.0">
    <meta name="csrf-token" content="<?= ($CSRF) ?>">
</head>

<body>
