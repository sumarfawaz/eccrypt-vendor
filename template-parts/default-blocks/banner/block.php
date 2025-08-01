<?php

if (is_front_page()) {
    get_template_part('template-parts/default-blocks/banner/home-banner');
} else {
    get_template_part('template-parts/default-blocks/banner/inner-banner');
}