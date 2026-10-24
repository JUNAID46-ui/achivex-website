<?php

// Design options added to every section: colours, background image, fonts, heading size,
// spacing and alignment. Empty values keep the website's own styling.
$fonts = ['' => 'Website default', 'Geist' => 'Geist', 'Manrope' => 'Manrope', 'Inter' => 'Inter', 'Poppins' => 'Poppins',
    'DM Sans' => 'DM Sans', 'Montserrat' => 'Montserrat', 'Playfair Display' => 'Playfair Display', 'Lora' => 'Lora', 'Space Grotesk' => 'Space Grotesk'];
return ['name' => 'design', 'label' => 'Design (colours, fonts and spacing)', 'type' => 'group', 'fields' => [
    ['name' => 'bg', 'label' => 'Background colour', 'type' => 'color'],
    ['name' => 'bgImage', 'label' => 'Background image (a dark overlay keeps text readable)', 'type' => 'image'],
    ['name' => 'heading', 'label' => 'Heading colour', 'type' => 'color'],
    ['name' => 'text', 'label' => 'Text colour', 'type' => 'color'],
    ['name' => 'accent', 'label' => 'Button and link colour', 'type' => 'color'],
    ['name' => 'headingFont', 'label' => 'Heading font', 'type' => 'select', 'options' => $fonts],
    ['name' => 'bodyFont', 'label' => 'Text font', 'type' => 'select', 'options' => $fonts],
    ['name' => 'headingSize', 'label' => 'Heading size', 'type' => 'select', 'options' => ['' => 'Default', '28' => 'Small (28px)', '36' => 'Medium (36px)', '48' => 'Large (48px)', '60' => 'Extra large (60px)', '72' => 'Huge (72px)']],
    ['name' => 'spacing', 'label' => 'Space above and below', 'type' => 'select', 'options' => ['' => 'Default', 'none' => 'None', 'compact' => 'Compact', 'spacious' => 'Spacious']],
    ['name' => 'align', 'label' => 'Text alignment', 'type' => 'select', 'options' => ['' => 'Default', 'left' => 'Left', 'center' => 'Centre', 'right' => 'Right']],
]];
