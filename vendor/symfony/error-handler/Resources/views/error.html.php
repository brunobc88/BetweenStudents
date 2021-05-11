<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="<?= $this->charset; ?>" />
    <meta name="robots" content="noindex,nofollow,noarchive" />
    <title>Oups: <?= $statusText; ?></title>
    <style><?= $this->include('assets/css/error.css'); ?></style>
</head>
<body>

<div class="container">
    <div class="row align-items-center" style="height: 100vh">
        <div class="col text-center">
            <span class="counter"><?= $statusCode; ?></span><br>
            <span class="errorType"><?= $statusText; ?></span><br>
            <span>Cela ne veut peut-être rien dire.</span><br>
            <span>On travaille probablement sur quelque chose qui a explosé.</span>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {

        $('.counter').each(function () {
            $(this).prop('Counter',0).animate({
                Counter: $(this).text()
            }, {
                duration: 2000,
                easing: 'swing',
                step: function (now) {
                    $(this).text(Math.ceil(now));
                }
            });
        });

    });
</script>

</body>
</html>
