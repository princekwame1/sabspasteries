<?php

namespace App\Support;

/**
 * Every piece of public-site text, image and section switch that can be edited in Admin → Site content.
 *
 * Each field is stored as a Setting under its key; the value here is the default shown until the admin
 * changes it. Text can use {business} and {tagline}, which are replaced with the brand settings.
 *
 * To make something new editable: add a field here, then use site('key') in the view.
 */
class SiteContent
{
    public const STORY = "At {business}, baking is more than just creating sweet treats; it’s about bringing people together, celebrating life’s moments and creating delicious memories one bite at a time.\n\nDriven by a passion for baking and creativity, {business} is dedicated to crafting delightful treats that are made with care and designed to make every occasion that little bit sweeter. Whether you’re looking for something to satisfy your cravings, a thoughtful gift or the perfect treats for a celebration, we’re here to make it special.\n\nWe believe that the sweetest moments are often the simplest ones, and there’s nothing quite like sharing something delicious with the people you love.\n\nFrom our kitchen to your table, every order is made with love, care and a touch of sweetness.";

    /** Icons the admin can pick for the service cards (icon name => label shown in the dropdown). */
    public const ICONS = [
        'star' => 'Star', 'sparkle' => 'Sparkle', 'heart' => 'Heart', 'pen' => 'Pen', 'calendar' => 'Calendar',
        'store' => 'Shop', 'truck' => 'Delivery van', 'users' => 'People', 'tag' => 'Tag', 'clock' => 'Clock', 'check' => 'Tick',
    ];

    /** @return array<string, array{label: string, sections: array<string, array{label: string, fields: array<string, array>}>}> */
    public static function pages(): array
    {
        $t = fn (string $label, string $default = '', ?string $hint = null) => ['type' => 'text', 'label' => $label, 'default' => $default, 'hint' => $hint];
        $area = fn (string $label, string $default = '', ?string $hint = null) => ['type' => 'textarea', 'label' => $label, 'default' => $default, 'hint' => $hint];
        $show = fn (string $label = 'Show this section') => ['type' => 'toggle', 'label' => $label, 'default' => '1', 'hint' => null];
        $img = fn (string $label, string $default, ?string $hint = null) => ['type' => 'image', 'label' => $label, 'default' => $default, 'hint' => $hint];
        $icon = fn (string $label, string $default) => ['type' => 'select', 'label' => $label, 'default' => $default, 'hint' => null, 'options' => self::ICONS];

        return [
            'home' => ['label' => 'Home page', 'sections' => [
                'hero' => ['label' => 'Hero (top of the page)', 'fields' => [
                    'hero_eyebrow' => $t('Small heading', 'Welcome to {business}'),
                    'hero_title' => $t('Headline', 'A little sweetness, *made with love*.', 'Wrap a word in *stars* to mark it as an accent word.'),
                    'hero_text' => $area('Intro text', 'Every treat is created to bring a little sweetness, joy and indulgence to life’s special moments.'),
                    'hero_image_path' => $img('Main photo', 'images/hero-gold-pearl-cake.webp', 'Portrait photos work best.'),
                    'hero_button' => $t('Main button', 'Order online'),
                    'hero_button2' => $t('Second button', 'See the menu', 'Leave blank to hide.'),
                    'hero_reviews_label' => $t('Text beside the star rating', 'Loved by our customers', 'Shown once you have published reviews.'),
                ]],
                'categories' => ['label' => 'Menu categories', 'fields' => [
                    'home_categories_show' => $show(),
                    'home_categories_eyebrow' => $t('Small heading', 'Our menu'),
                    'home_categories_title' => $t('Heading', 'Something sweet for *every moment*'),
                    'home_categories_button' => $t('Button on each tile', 'Browse'),
                ]],
                'featured' => ['label' => 'Favourites', 'fields' => [
                    'home_featured_show' => $show(),
                    'home_featured_eyebrow' => $t('Small heading', 'Customer favourites'),
                    'home_featured_title' => $t('Heading', 'Baked to order, *just for you*'),
                    'home_featured_button' => $t('Button', 'View the full menu'),
                ]],
                'services' => ['label' => 'Our services', 'fields' => [
                    'home_services_show' => $show(),
                    'home_services_eyebrow' => $t('Small heading', 'Our services'),
                    'home_services_title' => $t('Heading', 'For everyday indulgence and *special occasions*'),
                    'home_service_1_title' => $t('Service 1 heading', 'Pastries & baked treats', 'Leave a heading blank to hide that service.'),
                    'home_service_1_icon' => $icon('Service 1 icon', 'star'),
                    'home_service_1_text' => $area('Service 1 text', 'Delicious treats crafted to bring joy to every bite.'),
                    'home_service_2_title' => $t('Service 2 heading', 'Celebration cakes'),
                    'home_service_2_icon' => $icon('Service 2 icon', 'sparkle'),
                    'home_service_2_text' => $area('Service 2 text', 'Cakes to make birthdays, milestones and special celebrations unforgettable.'),
                    'home_service_3_title' => $t('Service 3 heading', 'Custom treats'),
                    'home_service_3_icon' => $icon('Service 3 icon', 'pen'),
                    'home_service_3_text' => $area('Service 3 text', 'Personalised creations tailored to your occasion and preferences.'),
                    'home_service_4_title' => $t('Service 4 heading', 'Dessert boxes'),
                    'home_service_4_icon' => $icon('Service 4 icon', 'heart'),
                    'home_service_4_text' => $area('Service 4 text', 'A delightful selection of sweet treats, perfect for gifting or sharing.'),
                    'home_service_5_title' => $t('Service 5 heading', 'Special occasion orders'),
                    'home_service_5_icon' => $icon('Service 5 icon', 'calendar'),
                    'home_service_5_text' => $area('Service 5 text', 'Sweet additions for parties, gatherings, celebrations and events.'),
                    'home_services_text' => $area('Closing line', 'Whether it’s a small treat or an order for a special celebration, we’re committed to making every experience with {business} a sweet one.', 'Leave blank to hide.'),
                ]],
                'steps' => ['label' => 'How ordering works', 'fields' => [
                    'home_steps_show' => $show(),
                    'home_steps_eyebrow' => $t('Small heading', 'How to order'),
                    'home_steps_title' => $t('Heading', 'Three easy steps to your *sweet treat*'),
                    'home_step_1_title' => $t('Step 1 heading', 'Choose your treat'),
                    'home_step_1_text' => $area('Step 1 text', 'Pick from the menu or tell us about your dream design. Share inspiration photos if you have them.'),
                    'home_step_2_title' => $t('Step 2 heading', 'We confirm the details'),
                    'home_step_2_text' => $area('Step 2 text', 'We get in touch to confirm the design, price and date. A deposit secures your slot.'),
                    'home_step_3_title' => $t('Step 3 heading', 'Collect or get it delivered'),
                    'home_step_3_text' => $area('Step 3 text', 'Freshly made for your day, ready to collect or delivered to your door.'),
                ]],
                'intro' => ['label' => 'About us teaser', 'fields' => [
                    'home_intro_show' => $show(),
                    'home_intro_eyebrow' => $t('Small heading', 'About {business}'),
                    'home_intro_title' => $t('Heading', 'From our kitchen *to your table*.'),
                    'home_intro_text' => $area('Text', 'Baking is more than just creating sweet treats; it’s about bringing people together, celebrating life’s moments and creating delicious memories one bite at a time.'),
                    'home_intro_image' => $img('Photo', 'images/about.jpg'),
                    'home_intro_button' => $t('Button', 'Our story', 'Links to the About page. Leave blank to hide.'),
                ]],
                'gallery' => ['label' => 'Gallery preview', 'fields' => [
                    'home_gallery_show' => $show(),
                    'home_gallery_eyebrow' => $t('Small heading', 'Fresh from the kitchen'),
                    'home_gallery_title' => $t('Heading', 'A little *sweet inspiration*'),
                    'home_gallery_button' => $t('Button', 'See the gallery'),
                ]],
                'reviews' => ['label' => 'Customer reviews', 'fields' => [
                    'home_reviews_show' => $show(),
                    'home_reviews_eyebrow' => $t('Small heading', 'Kind words'),
                    'home_reviews_title' => $t('Heading', 'What our customers *are saying*'),
                    'home_reviews_button' => $t('"Leave a review" link', 'Leave a review', 'Leave blank to hide.'),
                ]],
                'cta' => ['label' => 'Closing banner (bottom of every page)', 'fields' => [
                    'home_cta_show' => $show(),
                    'home_cta_eyebrow' => $t('Small heading', 'Get in touch'),
                    'home_cta_title' => $t('Heading', 'Let us make your next celebration *sweeter*!'),
                    'home_cta_text' => $area('Text', 'For orders, custom requests, enquiries or further information, we’d love to hear from you.'),
                    'home_cta_button' => $t('Main button', 'Order online'),
                    'home_cta_button2' => $t('WhatsApp button', 'Chat on WhatsApp', 'Leave blank to hide.'),
                ]],
            ]],

            'about' => ['label' => 'About page', 'sections' => [
                'intro' => ['label' => 'Our story', 'fields' => [
                    'about_eyebrow' => $t('Small heading', 'About us'),
                    'about_title' => $t('Heading', 'Bringing people together, *one bite at a time*.', 'Wrap a word in *stars* to mark it as an accent word.'),
                    'about_story_title' => $t('Story heading', 'Every order is made with *love and care*.'),
                    'about' => $area('Story', self::STORY, 'Leave a blank line between paragraphs.'),
                    'about_image_path' => $img('Photo', 'images/about-2.jpg'),
                    'about_button' => $t('Main button', 'Order online'),
                    'about_button2' => $t('Second button', 'See the menu', 'Leave blank to hide.'),
                ]],
                'values' => ['label' => 'What we believe', 'fields' => [
                    'about_values_show' => $show(),
                    'about_values_eyebrow' => $t('Small heading', 'What we believe'),
                    'about_values_title' => $t('Heading', 'Great taste, *awesome look*'),
                    'about_value_1_title' => $t('Value 1 heading', 'Made with care'),
                    'about_value_1_text' => $area('Value 1 text', 'Every cake, cupcake and treat is baked to order, never mass-produced.'),
                    'about_value_2_title' => $t('Value 2 heading', 'Creative designs'),
                    'about_value_2_text' => $area('Value 2 text', 'From vintage heart cakes to themed party cakes, we bring your ideas to life.'),
                    'about_value_3_title' => $t('Value 3 heading', 'Great taste'),
                    'about_value_3_text' => $area('Value 3 text', 'Because a cake should taste every bit as good as it looks.'),
                    'about_value_4_title' => $t('Value 4 heading', 'Sweet moments'),
                    'about_value_4_text' => $area('Value 4 text', 'Birthdays, weddings, gifts or simple cravings: we are here to make it special.'),
                ]],
                'reviews' => ['label' => 'Customer reviews', 'fields' => [
                    'about_reviews_show' => $show(),
                ]],
            ]],

            'menu' => ['label' => 'Menu page', 'sections' => [
                'intro' => ['label' => 'Introduction', 'fields' => [
                    'menu_eyebrow' => $t('Small heading', 'Our menu'),
                    'menu_title' => $t('Heading', 'Cakes, cupcakes & *sweet treats*'),
                    'menu_text' => $area('Text', 'Everything is made to order. Choose a favourite or ask us for something completely custom.'),
                    'menu_all' => $t('"All" filter label', 'Everything'),
                    'menu_empty' => $t('Empty text', 'New treats are coming soon. Message us to order something custom.'),
                    'menu_order_button' => $t('Order button', 'Order this'),
                    'menu_custom_title' => $t('Custom order heading', 'Can’t see what you’re *dreaming of*?'),
                    'menu_custom_text' => $area('Custom order text', 'Send us your idea and inspiration photos and we will create something just for you.'),
                    'menu_custom_button' => $t('Custom order button', 'Request a custom order'),
                    'menu_more' => $t('Heading for items without a category', 'More treats'),
                ]],
                'faq' => ['label' => 'Questions', 'fields' => [
                    'faq_show' => $show(),
                    'faq_eyebrow' => $t('Small heading', 'Good to know'),
                    'faq_title' => $t('Heading', 'Frequently asked questions'),
                    'faq_more_text' => $t('Line under the heading', 'Something else on your mind?', 'Leave blank to hide.'),
                    'faq_more_link' => $t('Link to the contact page', 'Ask us'),
                ]],
                'product' => ['label' => 'Product page', 'fields' => [
                    'product_ask_button' => $t('WhatsApp button', 'Ask a question', 'Leave blank to hide.'),
                    'product_notice' => $t('Notice line', 'Please order at least {days} ahead.', '{days} becomes e.g. “3 days”.'),
                    'product_related_eyebrow' => $t('“More treats” small heading', 'You may also love'),
                    'product_related_title' => $t('“More treats” heading', 'More *sweet treats*'),
                ]],
            ]],

            'gallery' => ['label' => 'Gallery page', 'sections' => [
                'intro' => ['label' => 'Introduction', 'fields' => [
                    'gallery_eyebrow' => $t('Small heading', 'Gallery'),
                    'gallery_title' => $t('Heading', 'Some of our *favourite bakes*'),
                    'gallery_text' => $area('Text', 'Birthdays, weddings, baby showers and everything in between. See something you love? Order one just like it.'),
                    'gallery_empty' => $t('Empty text', 'Photos are on their way. Check back soon.'),
                ]],
            ]],

            'order' => ['label' => 'Order page', 'sections' => [
                'intro' => ['label' => 'Introduction', 'fields' => [
                    'order_eyebrow' => $t('Small heading', 'Order online'),
                    'order_title' => $t('Heading', 'Let’s bake something *special*.'),
                    'order_text' => $area('Text', 'Tell us what you would like and when you need it. We will confirm the design, price and deposit with you before anything is final.'),
                ]],
                'form' => ['label' => 'Form', 'fields' => [
                    'order_step_1' => $t('Part 1 heading', 'What would you like?'),
                    'order_step_2' => $t('Part 2 heading', 'Your design'),
                    'order_step_3' => $t('Part 3 heading', 'When and how?'),
                    'order_step_4' => $t('Part 4 heading', 'Your details'),
                    'order_design_label' => $t('Design box label', 'Describe your design'),
                    'order_design_hint' => $t('Design box example', 'Colours, theme, decorations, number of tiers...'),
                    'order_button' => $t('Send button', 'Send my order'),
                    'order_note' => $area('Note under the button', 'Your order is confirmed once we have agreed the details and received your deposit.'),
                ]],
                'closed' => ['label' => 'When orders are paused', 'fields' => [
                    'order_closed_title' => $t('Heading', 'Orders are paused', 'The message under it is in Settings → Ordering.'),
                    'order_closed_button' => $t('WhatsApp button', 'Message us'),
                ]],
                'thanks' => ['label' => 'Thank-you page', 'fields' => [
                    'order_success_title' => $t('Thank-you heading', 'Thank you, {name}!'),
                    'order_summary_title' => $t('Order summary heading', 'Your order'),
                    'order_success' => $area('Thank-you message', 'Your order request {reference} is in. We will be in touch shortly to confirm the details, price and deposit.', '{name} is the customer’s first name and {reference} the order number.'),
                    'order_whatsapp_text' => $area('WhatsApp tip', 'Want a faster reply? Send your order to us on WhatsApp too. It is already filled in for you.', 'Leave blank to hide.'),
                    'order_whatsapp_button' => $t('WhatsApp button', 'Send on WhatsApp'),
                    'order_back_button' => $t('Back button', 'Back to the menu'),
                ]],
                'sidebar' => ['label' => 'Side panel', 'fields' => [
                    'order_steps_title' => $t('Steps heading', 'How it works', 'The steps themselves come from Home page → How ordering works.'),
                    'order_chat_title' => $t('Chat box heading', 'Prefer to chat?'),
                    'order_chat_text' => $area('Chat box text', 'Send us a DM or WhatsApp and we will help you plan your order.'),
                ]],
            ]],

            'contact' => ['label' => 'Contact page', 'sections' => [
                'intro' => ['label' => 'Introduction', 'fields' => [
                    'contact_eyebrow' => $t('Small heading', 'Get in touch'),
                    'contact_title' => $t('Heading', 'Let us make your next celebration *sweeter*!'),
                    'contact_text' => $area('Text', 'For orders, custom requests, enquiries or further information, we’d love to hear from you. Reach out to us via our social media pages to place an order or enquire about our creations.'),
                ]],
                'form' => ['label' => 'Form', 'fields' => [
                    'contact_message_label' => $t('Message box label', 'Your message'),
                    'contact_message_hint' => $t('Message box example', 'How can we help?'),
                    'contact_button' => $t('Send button', 'Send message'),
                    'contact_success_title' => $t('Thank-you heading', 'Message sent'),
                    'contact_success' => $area('Thank-you message', 'Thanks {name}! We will be in touch shortly.', '{name} is replaced with the visitor’s first name.'),
                    'contact_success_button' => $t('Button after sending', 'Browse the menu'),
                ]],
                'details' => ['label' => 'Contact cards', 'fields' => [
                    'contact_call_label' => $t('Phone label', 'Call'),
                    'contact_whatsapp_label' => $t('WhatsApp label', 'WhatsApp'),
                    'contact_email_label' => $t('Email label', 'Email'),
                    'contact_address_label' => $t('Address label', 'Where to find us'),
                    'contact_follow_label' => $t('Social links label', 'Follow us'),
                    'contact_cta_title' => $t('Order box heading', 'Ready to order?'),
                    'contact_cta_text' => $area('Order box text', 'Choose your treat, pick a date and share your design ideas in a couple of minutes.'),
                    'contact_cta_button' => $t('Order box button', 'Order online'),
                ]],
            ]],

            'reviews' => ['label' => 'Review page', 'sections' => [
                'page' => ['label' => 'Leave a review', 'fields' => [
                    'review_eyebrow' => $t('Small heading', 'Your feedback'),
                    'review_title' => $t('Heading', 'How was your *treat*?'),
                    'review_text' => $area('Text', 'Tell us about your order from {business}. Reviews appear on the site once we have checked them.'),
                    'review_button' => $t('Send button', 'Send review'),
                    'review_success_title' => $t('Thank-you heading', 'Thank you!'),
                    'review_success' => $area('Thank-you message', 'Thanks {name}! Your review will appear once we have checked it.'),
                ]],
            ]],

            'layout' => ['label' => 'Header & footer', 'sections' => [
                'nav' => ['label' => 'Menu', 'fields' => [
                    'nav_menu' => $t('Menu', 'Menu'),
                    'nav_gallery' => $t('Gallery', 'Gallery'),
                    'nav_about' => $t('About', 'About us'),
                    'nav_contact' => $t('Contact', 'Contact us'),
                    'nav_cta' => $t('Pink button', 'Order now'),
                    'nav_call' => $t('Before the phone number', 'Call', 'Used in the phone menu and footer, e.g. “Call +44 …”.'),
                    'nav_menu_show' => $show('Show “Menu”'),
                    'nav_gallery_show' => $show('Show “Gallery”'),
                    'nav_about_show' => $show('Show “About”'),
                    'nav_contact_show' => $show('Show “Contact”'),
                ]],
                'footer' => ['label' => 'Footer', 'fields' => [
                    'footer_text' => $area('Text under the logo', 'A little sweetness, made with love. Pastries, celebration cakes, custom treats and dessert boxes for every occasion.'),
                    'footer_explore' => $t('Column 1 heading', 'Menu'),
                    'footer_company' => $t('Column 2 heading', 'Explore'),
                    'footer_contact' => $t('Column 3 heading', 'Get in touch'),
                    'footer_order_link' => $t('“Order online” link', 'Order online'),
                    'footer_review_link' => $t('“Leave a review” link', 'Leave a review', 'Leave blank to hide.'),
                    'footer_links_link' => $t('“All our links” link', 'All our links', 'Shown when a Linktree address is set in Settings.'),
                    'footer_rights' => $t('After the copyright', 'All rights reserved.'),
                    'footer_note' => $t('Small print', 'Made with love, care and a touch of sweetness.'),
                ]],
                'chat' => ['label' => 'Order chat', 'fields' => [
                    'chat_button' => $t('Chat button', 'Order by chat'),
                    'chat_subtitle' => $t('Line under the name', 'Order assistant · usually replies in seconds'),
                    'chat_greeting' => $area('First message', 'Hi! I’m the {business} order assistant. Tell me what you’re celebrating and I’ll help you order. 🎂'),
                    'chat_suggestions' => $area('Suggested questions', "I’d like a birthday cake\nWhat do you make?\nHow much notice do you need?\nDo you deliver?", 'One per line. Customers tap these to start the chat. Leave blank to hide.'),
                    'chat_note' => $t('Small print', 'AI assistant · the bakery confirms every order personally'),
                ]],
                'follow' => ['label' => 'Follow-us pop-up (first visit)', 'fields' => [
                    'follow_show' => $show('Show the pop-up'),
                    'follow_eyebrow' => $t('Small heading', 'Hello there!'),
                    'follow_title' => $t('Heading', 'Follow {business} for sweet updates'),
                    'follow_text' => $area('Text', 'New bakes, behind-the-scenes and giveaways. Follow us so you never miss a treat.'),
                    'follow_image' => $img('Photo', 'images/hero-gold-pearl-cake.webp', 'Shown at the top of the pop-up.'),
                    'follow_delay' => $t('Wait before showing (seconds)', '6', 'Shown once per visitor. It never appears on the order pages.'),
                    'follow_dismiss' => $t('Close link', 'Maybe later'),
                ]],
                'qr' => ['label' => 'QR code “Follow us” page', 'fields' => [
                    'qr_eyebrow' => $t('Small heading', 'Thanks for scanning!'),
                    'qr_title' => $t('Heading', 'Follow {business}'),
                    'qr_text' => $area('Text', 'New bakes, behind-the-scenes and giveaways. Tap a button to follow us.'),
                    'qr_instagram' => $t('Instagram button', 'Follow us on Instagram'),
                    'qr_tiktok' => $t('TikTok line', 'Watch our bakes'),
                    'qr_threads' => $t('Threads line', 'Join the chat'),
                    'qr_facebook' => $t('Facebook line', 'Like our page'),
                    'qr_whatsapp' => $t('WhatsApp line', 'Questions or orders: message us'),
                    'qr_follow_button' => $t('“Follow” button', 'Follow'),
                    'qr_chat_button' => $t('“Chat” button', 'Chat'),
                    'qr_order_button' => $t('Order button', 'Place an order'),
                    'qr_site_button' => $t('Website button', 'Visit our website'),
                ]],
            ]],
        ];
    }

    /** @return array<string, array> key => field definition */
    public static function fields(): array
    {
        $all = [];
        foreach (self::pages() as $page) {
            foreach ($page['sections'] as $section) {
                $all += $section['fields'];
            }
        }

        return $all;
    }

    /** @return array<string, string> key => default value (images default to '' so a saved upload is distinguishable) */
    public static function defaults(): array
    {
        return array_map(fn ($f) => in_array($f['type'], ['image', 'video'], true) ? '' : $f['default'], self::fields());
    }

    /** Fields of one admin tab. */
    public static function pageFields(string $page): array
    {
        $fields = [];
        foreach (self::pages()[$page]['sections'] ?? [] as $section) {
            $fields += $section['fields'];
        }

        return $fields;
    }
}
