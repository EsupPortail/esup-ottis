<div class="swiper-container">
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
    <!-- Slider main container -->
    <div class="swiper">
        <!-- Additional required wrapper -->
        <div class="swiper-wrapper">
            <?php
                // Génération dynamique des slides
                foreach ($carrouselItems as $item) {
                    echo <<<HTML
                    <div class="swiper-slide">
                        <div class="slide-icon">{$this->escape($item['icon'])}</div>
                        <h3 class="translate" data-key="{$this->escape($item['title'])}">Titre</h3>
                        <p class="translate" data-key="{$this->escape($item['text'])}">Description</p>
                        <a href="{$this->escape($item['buttonLink'])}" class="slide-button translate" data-key="{$this->escape($item['buttonText'])}">Texte bouton</a>
                    </div>
                    HTML;
                }
            ?>
            </div>
        </div>
    </div>
</div>
