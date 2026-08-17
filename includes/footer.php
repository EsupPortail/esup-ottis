<!-- includes/footer.php -->
    </main>
    <footer>
        <!-- Ton footer commun -->
    </footer>
<?php if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'])) : ?>
    <script src="/js/tests/no-jquery-validation.js"></script>
<?php endif; ?>
</body>
</html>