<?php

// Editable fields for each home page section in the Page Builder.
// Field types: text, textarea, image, link, lines, repeater (with "fields"), case (case study picker).
return [
    'hero' => [
        'label' => 'Home hero',
        'description' => 'The first thing visitors see: label, heading, image, figures and button.',
        'fields' => [
            ['name' => 'pill', 'label' => 'Small label above the heading', 'type' => 'text'],
            ['name' => 'heading', 'label' => 'Heading', 'type' => 'text'],
            ['name' => 'text', 'label' => 'Supporting text', 'type' => 'textarea'],
            ['name' => 'image', 'label' => 'Image', 'type' => 'image'],
            ['name' => 'imageAlt', 'label' => 'Image description (alt text)', 'type' => 'text'],
            ['name' => 'buttonText', 'label' => 'Button text', 'type' => 'text'],
            ['name' => 'buttonLink', 'label' => 'Button link', 'type' => 'link'],
        ],
        'heroStats' => true,
    ],
    'intro' => [
        'label' => 'Intro statement',
        'description' => 'A large statement about the company. The highlighted words are underlined.',
        'fields' => [
            ['name' => 'before', 'label' => 'Text before the highlight', 'type' => 'textarea'],
            ['name' => 'highlight', 'label' => 'Highlighted words', 'type' => 'text'],
            ['name' => 'after', 'label' => 'Text after the highlight', 'type' => 'textarea'],
            ['name' => 'linkText', 'label' => 'Link text', 'type' => 'text'],
            ['name' => 'linkTo', 'label' => 'Link', 'type' => 'link'],
        ],
    ],
    'explorer' => [
        'label' => 'Service explorer',
        'description' => 'Interactive list of all service areas. Services come from the service pages.',
        'fields' => [
            ['name' => 'title', 'label' => 'Heading', 'type' => 'text'],
            ['name' => 'text', 'label' => 'Supporting text', 'type' => 'textarea'],
        ],
    ],
    'why' => [
        'label' => 'Why choose us',
        'description' => 'Reasons to choose AchiveX, on a dark background.',
        'fields' => [
            ['name' => 'title', 'label' => 'Heading', 'type' => 'text'],
            ['name' => 'text', 'label' => 'Supporting text', 'type' => 'textarea'],
            ['name' => 'items', 'label' => 'Reasons', 'type' => 'repeater', 'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text'],
                ['name' => 'text', 'label' => 'Text', 'type' => 'textarea'],
            ]],
        ],
    ],
    'spotlights' => [
        'label' => 'Service spotlights',
        'description' => 'Large image and text blocks that alternate sides.',
        'fields' => [
            ['name' => 'items', 'label' => 'Spotlights', 'type' => 'repeater', 'fields' => [
                ['name' => 'title', 'label' => 'Heading', 'type' => 'text'],
                ['name' => 'text', 'label' => 'Text', 'type' => 'textarea'],
                ['name' => 'points', 'label' => 'Bullet points (one per line)', 'type' => 'lines'],
                ['name' => 'image', 'label' => 'Image', 'type' => 'image'],
                ['name' => 'alt', 'label' => 'Image description', 'type' => 'text'],
                ['name' => 'link', 'label' => 'Button text', 'type' => 'text'],
                ['name' => 'to', 'label' => 'Button link', 'type' => 'link'],
            ]],
        ],
    ],
    'process' => [
        'label' => 'How we work',
        'description' => 'Numbered steps showing how engagements run.',
        'fields' => [
            ['name' => 'title', 'label' => 'Heading', 'type' => 'text'],
            ['name' => 'text', 'label' => 'Supporting text', 'type' => 'textarea'],
            ['name' => 'steps', 'label' => 'Steps', 'type' => 'repeater', 'fields' => [
                ['name' => 'title', 'label' => 'Step name', 'type' => 'text'],
                ['name' => 'text', 'label' => 'Description', 'type' => 'textarea'],
            ]],
        ],
    ],
    'industries' => [
        'label' => 'Industries',
        'description' => 'List of industries. Industry pages are part of the website code.',
        'fields' => [
            ['name' => 'title', 'label' => 'Heading', 'type' => 'text'],
            ['name' => 'text', 'label' => 'Supporting text', 'type' => 'textarea'],
        ],
    ],
    'platforms' => [
        'label' => 'Platforms and partners',
        'description' => 'Row of technology platforms linking to the alliance pages.',
        'fields' => [
            ['name' => 'title', 'label' => 'Heading', 'type' => 'text'],
            ['name' => 'note', 'label' => 'Small note', 'type' => 'text'],
        ],
    ],
    'featured_case' => [
        'label' => 'Featured case study',
        'description' => 'One case study shown large.',
        'fields' => [
            ['name' => 'caseSlug', 'label' => 'Case study to feature', 'type' => 'case'],
        ],
    ],
    'insights' => [
        'label' => 'Latest insights',
        'description' => 'Latest case study, two newest blog posts and a free guide.',
        'fields' => [
            ['name' => 'title', 'label' => 'Heading', 'type' => 'text'],
            ['name' => 'text', 'label' => 'Supporting text', 'type' => 'textarea'],
        ],
    ],
    'faq' => [
        'label' => 'Questions and answers',
        'description' => 'Questions and answers in an accordion.',
        'fields' => [
            ['name' => 'title', 'label' => 'Heading', 'type' => 'text'],
            ['name' => 'text', 'label' => 'Supporting text', 'type' => 'textarea'],
            ['name' => 'items', 'label' => 'Questions', 'type' => 'repeater', 'fields' => [
                ['name' => 'q', 'label' => 'Question', 'type' => 'text'],
                ['name' => 'a', 'label' => 'Answer', 'type' => 'textarea'],
            ]],
        ],
    ],
    'contact_strip' => [
        'label' => 'Contact details strip',
        'description' => 'Phone, email, location and Google profile. Edit these under Settings.',
        'fields' => [],
    ],
    'cta' => [
        'label' => 'Call to action band',
        'description' => 'The closing call to action band.',
        'fields' => [
            ['name' => 'title', 'label' => 'Heading', 'type' => 'text'],
            ['name' => 'text', 'label' => 'Text', 'type' => 'textarea'],
            ['name' => 'button', 'label' => 'Button text', 'type' => 'text'],
        ],
    ],

    // General sections for new pages and for extra sections on any page.
    'page_hero' => [
        'label' => 'Page heading (hero)',
        'description' => 'Large page title with text, an optional image and button.',
        'fields' => [
            ['name' => 'heading', 'label' => 'Heading', 'type' => 'text'],
            ['name' => 'text', 'label' => 'Text', 'type' => 'textarea'],
            ['name' => 'image', 'label' => 'Image (optional)', 'type' => 'image'],
            ['name' => 'imageAlt', 'label' => 'Image description (alt text)', 'type' => 'text'],
            ['name' => 'buttonText', 'label' => 'Button text', 'type' => 'text'],
            ['name' => 'buttonLink', 'label' => 'Button link', 'type' => 'link'],
            ['name' => 'style', 'label' => 'Background', 'type' => 'select', 'options' => ['' => 'White', 'dark' => 'Black']],
        ],
        'defaults' => ['heading' => 'Page title', 'text' => 'A short introduction to this page.', 'buttonText' => '', 'buttonLink' => '/contact-us/', 'style' => ''],
    ],
    'rich_text' => [
        'label' => 'Text block',
        'description' => 'A heading with paragraphs of text.',
        'fields' => [
            ['name' => 'heading', 'label' => 'Heading', 'type' => 'text'],
            ['name' => 'paragraphs', 'label' => 'Paragraphs (one per line)', 'type' => 'lines'],
            ['name' => 'buttonText', 'label' => 'Button text (optional)', 'type' => 'text'],
            ['name' => 'buttonLink', 'label' => 'Button link', 'type' => 'link'],
            ['name' => 'background', 'label' => 'Background', 'type' => 'select', 'options' => ['' => 'White', 'grey' => 'Light grey']],
        ],
        'defaults' => ['heading' => 'Section heading', 'paragraphs' => ['Write your text here.'], 'background' => ''],
    ],
    'image_text' => [
        'label' => 'Image with text',
        'description' => 'An image on one side and text on the other.',
        'fields' => [
            ['name' => 'heading', 'label' => 'Heading', 'type' => 'text'],
            ['name' => 'text', 'label' => 'Text', 'type' => 'textarea'],
            ['name' => 'image', 'label' => 'Image', 'type' => 'image'],
            ['name' => 'imageAlt', 'label' => 'Image description (alt text)', 'type' => 'text'],
            ['name' => 'imageSide', 'label' => 'Image position', 'type' => 'select', 'options' => ['left' => 'Image on the left', 'right' => 'Image on the right']],
            ['name' => 'buttonText', 'label' => 'Button text (optional)', 'type' => 'text'],
            ['name' => 'buttonLink', 'label' => 'Button link', 'type' => 'link'],
            ['name' => 'background', 'label' => 'Background', 'type' => 'select', 'options' => ['' => 'White', 'grey' => 'Light grey']],
        ],
        'defaults' => ['heading' => 'Section heading', 'text' => 'Write your text here.', 'imageSide' => 'left'],
    ],
    'cards' => [
        'label' => 'Cards grid',
        'description' => 'A heading and a grid of cards, each with a title, text and optional image.',
        'fields' => [
            ['name' => 'heading', 'label' => 'Heading', 'type' => 'text'],
            ['name' => 'text', 'label' => 'Text', 'type' => 'textarea'],
            ['name' => 'items', 'label' => 'Cards', 'type' => 'repeater', 'fields' => [
                ['name' => 'title', 'label' => 'Title', 'type' => 'text'],
                ['name' => 'text', 'label' => 'Text', 'type' => 'textarea'],
                ['name' => 'image', 'label' => 'Image (optional)', 'type' => 'image'],
            ]],
            ['name' => 'background', 'label' => 'Background', 'type' => 'select', 'options' => ['' => 'White', 'grey' => 'Light grey']],
        ],
        'defaults' => ['heading' => 'Section heading', 'items' => [['title' => 'Card title', 'text' => 'Card text.']]],
    ],
    'gallery' => [
        'label' => 'Image gallery',
        'description' => 'A grid of images.',
        'fields' => [
            ['name' => 'heading', 'label' => 'Heading (optional)', 'type' => 'text'],
            ['name' => 'images', 'label' => 'Images', 'type' => 'images'],
        ],
        'defaults' => ['heading' => '', 'images' => []],
    ],
];
