<footer>
    <p>&copy; <?= e((new DateTimeImmutable('now', site_timezone_object($config)))->format('Y')) ?> <?= e($config['site_title']) ?></p>
</footer>

<script data-goatcounter="https://maira.goatcounter.com/count"
        async src="//gc.zgo.at/count.js"></script>