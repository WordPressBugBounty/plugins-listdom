<?php
// no direct access
defined('ABSPATH') || die();
?>
<div class="lsd-changelog-wrap">
    <h2>v5.6.0 <span>June 7th, 2026</span></h2>
    <ul class="lsd-changelog">
        <li><?php esc_html_e('Added AI-powered search with structured and semantic options, model settings, and connector approval notices.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added a Back Button element for single listing pages.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added new single listing controls for attribute visibility, listing links, and timeline skin output in connected shortcodes.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added week-start options for date pickers and improved date fields in search forms.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added an image-centered taxonomy shortcode style.', 'listdom'); ?></li>
        <li><?php esc_html_e('Improved map and direction button behavior, including refreshed cluster icons and cleaner Google map interactions.', 'listdom'); ?></li>
        <li><?php esc_html_e('Refined frontend and backend UI details for search fields, modals, tooltips, badges, icons, and listing inputs.', 'listdom'); ?></li>
        <li><?php esc_html_e('Improved AI search reliability with better caching, duplicate job prevention, and semantic search interface polish.', 'listdom'); ?></li>
        <li><?php esc_html_e('Fixed import, API, and listing save reliability issues, including attribute slugs, metadata overwrite protection, and file size enforcement.', 'listdom'); ?></li>
        <li><?php esc_html_e('Fixed duplicate notifications, frontend auth role handling, and safer listing data handling.', 'listdom'); ?></li>
    </ul>
    <h2>v5.5.0 <span>May 12th, 2026</span></h2>
    <ul class="lsd-changelog">
        <li><?php esc_html_e('Updated Italian translations for Listdom and its addons.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added more flexible date and time options for custom fields and search, along with more reliable imported values.', 'listdom'); ?></li>
        <li><?php esc_html_e('Enhanced the security of frontend and backend elements and fixed several related issues.', 'listdom'); ?></li>
        <li><?php esc_html_e('Improved listing imports with better defaults for work hours and cleaner handling of listing statuses.', 'listdom'); ?></li>
        <li><?php esc_html_e('Enhanced the Licenses menus in the Listdom backend.', 'listdom'); ?></li>
        <li><?php esc_html_e('Improved sticky sidebar behavior, popup interactions, and admin settings screens for a smoother experience.', 'listdom'); ?></li>
        <li><?php esc_html_e('Refined listing cards and review displays for a cleaner frontend appearance.', 'listdom'); ?></li>
        <li><?php esc_html_e('Improved dashboard reliability with better role recovery and profile image handling.', 'listdom'); ?></li>
        <li><?php esc_html_e('Fixed privacy handling in RSS feeds, Stripe checkout notices, geo-search filtering, and several sharing and interface issues.', 'listdom'); ?></li>
    </ul>
    <h2>v5.4.0 <span>April 3rd, 2026</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released the %s addon to help you build your Listdom directory using Bricks Builder.', 'listdom'),
                '<strong>'.esc_html__('Bricks', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: App name. */
                esc_html__('[APP] Released the %s app, allowing agencies and site owners to replace Listdom branding.', 'listdom'),
                '<strong>'.esc_html__('White Label', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li><?php esc_html_e('Added FAQ schema support.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added thousands and decimal separator options for better currency localization.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added "File" type to Listdom custom fields.', 'listdom'); ?></li>
        <li><?php esc_html_e('Improved shortcode, search, and builder filters with cleaner attribute slug handling across listing integrations.', 'listdom'); ?></li>
        <li><?php esc_html_e('Enhanced listing management with more reliable primary category handling, improved category-based filtering, and more stable export sorting.', 'listdom'); ?></li>
        <li><?php esc_html_e('Improved the admin experience with clearer notifications, more responsive shortcode builder feedback, and consistent branded menu behavior.', 'listdom'); ?></li>
        <li><?php esc_html_e('Enhanced account recovery and contact-related flows for more reliable frontend login and profile interactions.', 'listdom'); ?></li>
        <li><?php esc_html_e('Fixed issues with rich custom field formatting and category-restricted attributes on single listing pages.', 'listdom'); ?></li>
        <li><?php esc_html_e('Fixed several interface issues, including iOS search field styling and admin notifications.', 'listdom'); ?></li>
    </ul>
    <h2>v5.3.0 <span>February 25th, 2026</span></h2>
    <ul class="lsd-changelog">
        <li><?php esc_html_e('Added Mobile App style integration to the search builder with filterable search styles and improved desktop tab behavior.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added hierarchical category and location import support for CSV and Excel mapping.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added taxonomy label overrides with locale-aware settings and improved label handling across shortcode filters and import mapping.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added API improvements including the price classes REST endpoint and listing URL in API listing payloads.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added WP All Import map synchronization support for listing location data.', 'listdom'); ?></li>
        <li><?php esc_html_e('Improved Frontend Dashboard UX with image aspect ratio controls and clearer maximum image size guidance.', 'listdom'); ?></li>
        <li><?php esc_html_e('Improved authentication, custom field, and Select2 UI consistency across backend and frontend forms.', 'listdom'); ?></li>
        <li><?php esc_html_e('Fixed payment flow issues including checkout privacy consent persistence and payments context handling in menus and exports.', 'listdom'); ?></li>
        <li><?php esc_html_e('Fixed multiple UI and search issues across skins and widgets, including empty-state alerts and taxonomy slug sync behavior.', 'listdom'); ?></li>
    </ul>
    <h2>v5.2.1 <span>February 1st, 2026</span></h2>
    <ul class="lsd-changelog">
        <li><?php esc_html_e('Added new options to customize Frontend Dashboard menus.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added infowindow trigger and mouse wheel zoom settings to map options in shortcodes.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added options to enforce listing status for new submissions via the Frontend Dashboard.', 'listdom'); ?></li>
        <li><?php esc_html_e('Optimized CSV export performance for large-scale websites.', 'listdom'); ?></li>
        <li><?php esc_html_e('Enhanced JSON and CSV import/export functionality with support for new data fields.', 'listdom'); ?></li>
        <li><?php esc_html_e('Resolved various issues in the search widget.', 'listdom'); ?></li>
        <li><?php esc_html_e('Improved the user interface for search inputs, map markers, and call-to-action buttons.', 'listdom'); ?></li>
    </ul>
    <h2>v5.2.0 <span>January 1st, 2026</span></h2>
    <ul class="lsd-changelog">
        <li><?php esc_html_e('Added global and region-based tax options to the Listdom payment system.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added an FAQ element for listings.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added the ability to import and export working hours in JSON, CSV, and Excel formats.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added an option to control the login visibility of custom menus in the Frontend Dashboard.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added an option to customize the URL slug of the listings RSS feed.', 'listdom'); ?></li>
        <li><?php esc_html_e('Enhanced recurring payment functionality in Listdom.', 'listdom'); ?></li>
        <li><?php esc_html_e('Improved the Table skin.', 'listdom'); ?></li>
        <li><?php esc_html_e('Fixed an issue with importing custom field values.', 'listdom'); ?></li>
        <li><?php esc_html_e('Fixed several issues related to maps and directions features.', 'listdom'); ?></li>
        <li><?php esc_html_e('Resolved multiple interface issues in both the Listdom backend and frontend.', 'listdom'); ?></li>
    </ul>
    <h2>v5.1.0 <span>November 28th, 2025</span></h2>
    <ul class="lsd-changelog">
        <li><?php esc_html_e('Added the ability to export/import custom fields and listing taxonomies using the CSV exporter/importer.', 'listdom'); ?></li>
        <li><?php esc_html_e('[PRO] Added the ability to export/import custom fields and listing taxonomies using the JSON exporter/importer.', 'listdom'); ?></li>
        <li><?php esc_html_e('[PRO] Improved the Timeline skin.', 'listdom'); ?></li>
        <li><?php esc_html_e('Enhanced user email verification by adding the option to resend the verification link.', 'listdom'); ?></li>
        <li><?php esc_html_e('Fixed an issue with displaying custom fields in the Listdom API.', 'listdom'); ?></li>
        <li><?php esc_html_e('Fixed field and title visibility issues in the search module.', 'listdom'); ?></li>
        <li><?php esc_html_e('Resolved an issue affecting the shortcode builder under certain conditions.', 'listdom'); ?></li>
        <li><?php esc_html_e('Fixed an issue with listing editing in the "Frontend Dashboard".', 'listdom'); ?></li>
        <li><?php esc_html_e('Resolved an issue with allowing HTML content in "Frontend Dashboard" custom menus.', 'listdom'); ?></li>
        <li><?php esc_html_e('Fixed several issues in the Listdom payment engine.', 'listdom'); ?></li>
        <li><?php esc_html_e('Resolved multiple interface issues in both the Listdom backend and frontend.', 'listdom'); ?></li>
    </ul>
    <h2>v5.0.1 <span>November 9th, 2025</span></h2>
    <ul class="lsd-changelog">
        <li><?php esc_html_e('Fixed an issue in "Locate Me" feature.', 'listdom'); ?></li>
    </ul>
    <h2>v5.0.0 <span>November 8th, 2025</span></h2>
    <ul class="lsd-changelog">
        <li><?php esc_html_e('Added new, modern styles for the listing editor in both the backend and frontend.', 'listdom'); ?></li>
        <li><?php esc_html_e('Introduced a fully configurable RSS feature for displaying listings.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added a "Locate Me" option for address and radius searches.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added responsive column controls to the Table skin.', 'listdom'); ?></li>
        <li><?php esc_html_e('Added new UI options, including menu type and column settings, for the frontend dashboard.', 'listdom'); ?></li>
        <li><?php esc_html_e('Enhanced the radius search feature.', 'listdom'); ?></li>
        <li><?php esc_html_e('Resolved various search and AJAX issues in the Masonry skin.', 'listdom'); ?></li>
        <li><?php esc_html_e('Fixed an unintended visibility issue affecting the Call to Action feature.', 'listdom'); ?></li>
        <li><?php esc_html_e('Fixed an issue related to saving advanced slugs.', 'listdom'); ?></li>
        <li><?php esc_html_e('Fixed several issues affecting custom CSS code handling.', 'listdom'); ?></li>
    </ul>
    <h2>v4.9.0 <span>October 22nd, 2025</span></h2>
    <ul class="lsd-changelog">
        <li><?php esc_html_e('Added the built-in Listdom payment engine.', 'listdom'); ?></li>
        <li><?php esc_html_e('Introduced a comprehensive Call to Action component to display a CTA button on single listing and search results pages.', 'listdom'); ?></li>
        <li><?php esc_html_e('[PRO] Added support for the Stripe payment gateway.', 'listdom'); ?></li>
        <li><?php esc_html_e('[PRO] Enabled recurring payments for memberships and subscriptions.', 'listdom'); ?></li>
        <li><?php esc_html_e('Enhanced the UI and responsiveness of pagination and sort bar components.', 'listdom'); ?></li>
        <li><?php esc_html_e('Resolved multiple interface issues in both the Listdom backend and frontend.', 'listdom'); ?></li>
    </ul>
    <h2>v4.8.1 <span>October 1st, 2025</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Addressed some background issues.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v4.8.0 <span>September 29th, 2025</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added a Timeline skin.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added comprehensive GDPR compliance.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved various parts of the plugin based on WordPress team feedback.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue with the standalone add listing form.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed several interface issues in the Listdom backend and frontend.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v4.7.1 <span>September 8th, 2025</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some interface issues in the Listdom backend and frontend.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v4.7.0 <span>September 5th, 2025</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added a Gallery skin.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added Gemini 2.5 Flash and Gemini 2.5 Flash Lite to the Listdom AI models.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added Anthropic Claude Sonnet 4 and Anthropic Claude Haiku 3.5 to the Listdom AI models.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Introduced AI module configuration options.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added new icons to the Listdom icon picker field.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Rebuilt the Import/Export, Add-ons, and Licenses menus in the Listdom backend.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the Listdom settings, shortcode builder, and other backend interfaces.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Enhanced the Listdom customizer by adding new controls and a unit selector.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed a login issue in the Listdom login shortcode.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed several minor UI display issues throughout the plugin.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v4.6.0 <span>August 16th, 2025</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added image field type for custom fields.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added telephone field type for custom fields.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added GPT-5 AI models.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added role-based redirection pages for the authentication feature.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added a frontend dashboard search widget.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added link labels for URL, telephone, and email custom fields.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the Listdom frontend bar and backend header.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Enhanced the look and feel of Listdom skins.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Disabled scroll wheel zoom on Leaflet maps.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed various issues in the frontend dashboard.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v4.5.1 <span>July 21st, 2025</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some issues related to the search module.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v4.5.0 <span>July 19th, 2025</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added OpenStreetMap integration to Listdom Core.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Introduced listing visibility control to Listdom Core.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Enabled creating new categories, locations, etc., directly from the frontend dashboard.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Introduced the Related Listings feature.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added Google and OpenStreetMap address autocomplete support.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added an advanced components system to disable unused Listdom Core features.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Integrated Mailchimp subscription support.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Improved AI tools with automatic content and business hours generation.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added an option to create categories without icons.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Completely rebuilt the settings menu and shortcode builder in the Listdom backend.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Added padding controls and made various UI refinements.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed several issues with search fields.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v4.4.0 <span>June 13th, 2025</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added AI integration and support for creating multiple AI profiles tailored to different tasks.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Introduced AI-powered field mapping for CSV and Excel imports.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Redesigned the Listdom admin page for improved usability.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Introduced radio and checkbox input types to Listdom custom fields.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added a "Clear All" button and enhanced the logic behind search forms.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added support for slug and parent fields in CSV/Excel import and export.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the custom fields editor and various UI elements.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed several issues related to pagination controls in skins.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Resolved various UI issues.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v4.3.0 <span>May 27th, 2025</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("[PRO] Added Mosaic and Accordion skins.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added CSV Import/Export functionality to Listdom Lite.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Introduced a numeric pagination option.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added a breadcrumb element to single listing templates.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Enabled pagination support for the Masonry skin.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added a column width control for the Table skin.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e("Improved custom fields UI, naming conventions, and overall user experience.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e("Fixed issues related to listing and map-based searches.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e("Resolved various UI inconsistencies in both the frontend and backend interfaces.", 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v4.2.0 <span>May 11th, 2025</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Moved custom fields feature from Listdom Pro to Listdom Lite.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Introduced responsive search builder to create different search forms per device type.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added new UI Customizer options for price fields and user profiles.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added an option to exclude the featured image from the listing gallery.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added camera control options to the map widget.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added dropdown and checkbox field types for tags in the frontend dashboard.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added linear layout option for the listing gallery element.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added an option to customize the search button label.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e("Improved responsiveness of search forms and range slider fields on the frontend.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e("Enhanced dropdown field usability in the frontend dashboard.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e("Fixed various UI issues in the table skin.", 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v4.1.2 <span>April 14th, 2025</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e("Enhanced the settings importer / exporter.", 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v4.1.1 <span>April 11th, 2025</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e("Fixed a couple of minor issues.", 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v4.1.0 <span>April 10th, 2025</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added public profile functionality.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added user profile settings to the frontend dashboard.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Introduced new user directory shortcode.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added support to open additional search options in a popup.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added a setting to customize the "More Options" label in the search widget.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added new customization options in the UI Customizer.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("[PRO] Added the ability to exclude listings from shortcodes by tags or authors.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added an option to display checkbox and radio fields in multiple columns in the search widget.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e("Enhanced the performance of sliders and carousels.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e("Improved the Map Bar responsiveness on smaller screens.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e("Resolved search widget positioning issues in specific skins.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e("Fixed a conflict with advanced asset loading feature.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e("Fixed an issue where listing status could not be updated from the frontend dashboard.", 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v4.0.0 <span>March 16th, 2025</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added a comprehensive Game-Changer UI Customizer to Listdom Core.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("[PRO] Introduced a new option to set a custom Single Listing Style for the Side by Side skin.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("[PRO] Added a new option to customize the Single Listing Style for the Lightbox feature.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("[PRO] Enabled an option to display the Single Listing Page in right, left, or bottom panels.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added a built-in Authentication Form to the Frontend Dashboard.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Introduced an option to make a shortcode non-searchable.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added a Zoom Level option for the Single Map element.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added a Title Visibility option for search fields.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Introduced the Listdom Bar to help web designers easily access Listdom features from the frontend.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Enabled the ability to update Listing Status in the Frontend Dashboard.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added a Listing Status Filter to the Frontend Dashboard.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Introduced an option to set Custom Listing Cards in the Half Map skin.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e("Improved the Table Skin for better usability.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e("[PRO] Enhanced the Side by Side skin with additional refinements.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e("Applied various UI and responsiveness improvements across Listdom views.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e("[PRO] Fixed issues related to Hierarchical Dropdowns.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e("Fixed compatibility issues with PHP 7.4 and lower.", 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.9.0 <span>February 9th, 2025</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added a Connected Shortcodes feature in the search builder, allowing multiple shortcodes to update dynamically with a single search widget.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Introduced a new style for List, Grid, Masonry, and List + Grid skins.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Enabled embedding via URL for the Embed and Featured Video elements.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Introduced a new option to control the visibility of search fields.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added the ability to restrict certain user roles from accessing the WordPress backend.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added an Image Fit option to applicable skins.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added an option to adjust the map height across different shortcodes.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added listing excerpts to Listdom fields and frontend dashboard modules.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Added a Thumbnail Status feature to the gallery element.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e("Moved the entire Listdom Pro authentication feature to Listdom core.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e("Improved the appearance and usability of search widgets.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e("Enhanced the Listdom settings menu for better navigation.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e("Improved the user interface and responsiveness of various elements.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e("Fixed search-related issues on archive pages.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e("Fixed an issue where multiple search widgets with More Options caused conflicts on the same page.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e("Resolved various AJAX search issues in Table and Masonry skins.", 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.8.1 <span>December 30th, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue with settings import functionality.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Resolved a problem with the Frontend Dashboard.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.8.0 <span>December 29th, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added an option to use Listdom authentication pages instead of WordPress default pages.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Introduced an advanced permalink system to include categories and locations in listing URLs.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("[PRO] Added the ability to connect different shortcodes to the Singlemap skin, allowing search results to update dynamically based on the map's position.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e("Introduced the 'Optimize Assets Loading' feature to suppress Listdom CSS/JS file loading on specific WordPress pages.", 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Moved the Frontend Dashboard feature from the Pro add-on to the Listdom core.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added an option to set custom titles for elements on the single listing page.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added left and right map display positions for applicable skins.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Introduced the settings export to create JSON backups of Listdom options.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added settings import to restore Listdom settings.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added the ability to customize the placeholder for the address field in both the backend and frontend.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Introduced a control option for the phone and name fields in the listing contact and report abuse forms.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added an option to disable the map info-window on the single listing page.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Enhanced accessibility across Listdom.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved backend management for listings and notifications.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Refined the structure and organization of the settings menu and setting tabs.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the user interface of the search builder.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Resolved several responsive UI issues.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue with clearing selections in the search widget.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Addressed a problem with WordPress Multisite integration.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some icon display issues.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v3.7.2 <span>December 10th, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Enhanced the performance of the Cover skin for better speed.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Refined the Welcome Wizard and updated button styles for improved usability.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Resolved one click handler issue to ensure smoother interactions.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue with the include/exclude filter options for better usability.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('[PRO] Corrected a problem with price components when the price class was globally disabled.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('[PRO] Fixed some issues in dashboard menus for improved customization.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.7.1 <span>December 1st, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Refined the user interface of the settings panel for a more intuitive experience.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Enhanced the security measures for the search widget.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.7.0 <span>November 26th, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added a new feature to dynamically add custom menus to the "Frontend Dashboard."', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Introduced the "Exclusion Filter" to exclude listings with specific categories, locations, features, and labels from search results.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Implemented the "Price Components" feature to enable or disable specific price elements.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added the "Custom Fields Filter" to allow filtering of listings based on attributes (custom fields) at the shortcode level.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Enabled bulk layout changes for specific listings using WordPress quick edit.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Introduced the ability to reorder "Frontend Dashboard" menus.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added an option to control the description length in applicable skins.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Introduced a new element to display listing excerpts.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added an option to customize the dropdown style of search fields.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Introduced a revamped interface for selecting different skins in shortcodes.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the overall design and usability of the frontend dashboard.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Enhanced the structure and visual appeal of the settings menu in the backend.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the responsiveness of the "Side by Side" skin.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Enhanced the Listdom welcome wizard by adding a theme installation step.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Resolved an issue with customizing display options in WordPress Multisite environments.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed a display issue where the map appeared incorrectly when set to the bottom position in list and grid skins.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.6.0 <span>October 27th, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added a shortcode for an independent "Add Listing" form.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added responsive view support for the "Side by Side" skin.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Introduced support for listing excerpts.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Enabled featured image display in style 3 of single listing.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the listing contact element.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Refined the settings loader style in the Listdom backend.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Enhanced the structure of "Masonry" and "Halfmap" skins for better performance and usability.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Resolved an issue in the Frontend Dashboard.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Fixed several issues related to listing schema.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Corrected various issues within the search module.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Addressed UI and responsiveness issues across different views.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed certain issues with required attributes in listings editors.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.5.0 <span>October 6th, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Introduced authentication features including login, registration, and password recovery.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added the option to customize the single listing layout per category.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Implemented the ability to restrict image size in the frontend dashboard.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Introduced a table builder feature for the table skin.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added numerous configurable display options across different skins.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Introduced a welcome setup wizard.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added a new "Listdom Publisher" user role with the capability to publish listings.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Introduced an option to add the featured image to the gallery element.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added the option to hide attribute titles within the attributes element.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Introduced a layout option for the features element.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added a new option to customize the sort bar layout.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('[PRO] Enhanced per-listing display options.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Streamlined the licensing and activation process.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the search and filter form builder.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Optimized the dummy data importer.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Resolved display option issues in WP Multisite.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Addressed issues with pre-made layouts in single listing.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed various UI and responsiveness issues.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.4.0 <span>September 3rd, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Connect', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added a design builder for creating flexible and visually appealing single listing pages.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added Style 3 and Style 4 for single listing pages.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added a slider type for the gallery element.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added a new sort option based on listing visits.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added duplicate listing, duplicate shortcode, duplicate notification, and duplicate search features.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added a dedicated key for the Google Geocoding API.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added listing featured video element.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('[PRO] Improved frontend dashboard configurations.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the responsive view of skins, lightbox, and the frontend dashboard.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved search widget styles.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Enhanced the dummy data importer.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed various UI issues.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Resolved some issues in search and filter widgets.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.3.2 <span>June 18th, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some visual issues.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.3.1 <span>June 15th, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some issues.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.3.0 <span>June 14th, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added listing author role for ease of user management in frontend submission.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added lightbox option to listing link methods.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added maximum gallery images to the frontend listing submission.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added maximum description length to the frontend listing submission.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added maximum number of tags to the frontend listing submission.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved Listdom interfaces in backend and frontend.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved loading of Google Maps API.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved Listdom settings menu.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some issues in trial and license activation.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an scroll issue in sortbar.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed issue of not displaying the success and error messages in listdom dashboard.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue when latitude and / or longitude are wrong.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.2.0 <span>March 23rd, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added an option to disable contact form of owner element.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved Listdom interfaces in WordPress backend.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the license activation section.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in single map skin.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.1.0 <span>February 15th, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added side by side skin.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added sort by price option.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Added new icons for Listdom and listings menus.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved Listdom dashboard.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the block editor integration.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some issues in single listing page.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in assigning guest user to listing.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some issues in image slider mode of listing image.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.0.4 <span>January 21st, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the server requirements.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.0.3 <span>January 19th, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some PHP issues in certain conditions.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.0.2 <span>January 18th, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the image gallery slider.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some issues.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.0.1 <span>January 13th, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some UI issues.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v3.0.0 <span>January 8th, 2024</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Rebranded to Webilia.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some PHP issues.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v2.6.0 <span>March 25th, 2023</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Added telegram to the social options.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Added left, right, and bottom positions for the search in the shortcode.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Added Hierarchy structure to the checkbox field of the search form builder.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Fixed an issue related to the empty values.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v2.5.0 <span>October 30th, 2022</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Network', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Ads', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added ability to display image slider in archive shortcodes instead of featured image.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the listing translations.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the compatibility with some third party plugins including page builder plugins.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some issues in multilingual websites.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v2.4.0 <span>April 24th, 2022</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added an ability to manage required fields for frontend submission.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added an ability to display radius field in search form.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added radius-dropdown method to radius search.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added WhatsApp, tiktok, and youtube to the social networks.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added infinite scroll pagination method to list, grid, listgrid, halfmap and table skins.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Disabled auto GPS when a geo request is made by user.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the interface of filter options in shortcode builder.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some multilingual issues.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed a compatibility issue with Avada theme.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v2.3.0 <span>December 26th, 2021</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added new map routes to the API.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added an ability to disable marker click on maps.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the map render time.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Disabled one finger drag in leaflet map for mobile devices.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in search widget in some multilingual websites.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in primary category.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in marker lightbox.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v2.2.0 <span>October 10th, 2021</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Elementor Compatibility', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Divi Builder Compatibility', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added webp image support.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added pagination to frontend dashboard.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('[PRO] Fixed some issues in hierarchical dropdown.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue regarding style 1 of single listing page.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v2.1.1 <span>June 27th, 2021</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added Instagram to social network options.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('[PRO] Improved guest user listing submission.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the social network options.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some PHP notices.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in status change notification.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v2.1.0 <span>April 8th, 2021</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added an option to change date format of date picker fields.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added an option to disable "Listing Link" field.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added HTML editor to the remark field.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added an ability to switch languages in REST API.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added some new endpoints to the REST API for multilingual websites.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue regarding halfmap skin.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in search module.', 'listdom'); ?></span>
        </li>
    </ul>
    <h2>v2.0.0 <span>February 27th, 2021</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Franchise', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Compare', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('APS', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Stats', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added an ability to disable image display per short-code.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added a feature to change the listing link method with normal, blank, and disabled options.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added new notification for listing status update.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added report abuse element.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some issues regarding schema feature.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some issues.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.9.0 <span>November 8th, 2020</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('ACF Integration', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Auction', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('BuddyPress Integration', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('KML', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added price class feature.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added an option to display human readable criteria to the search module.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added an option to change the currency position.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added style 3 to list, grid, listgrid, and halfmap skins.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added style 5 to carousel skin.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added style 4 to cover skin.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added style 3 to masonry skin.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added style 3 to table skin.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added website field to contact details of listing and owner.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Disabled scroll wheel on leaflet map.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.8.0 <span>September 4th, 2020</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Booking', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Multiple Categories', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Advanced Icon', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Listing Visibility', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added an option to load locations and features in multiple dropdown instead of checkboxes in frontend dashboard.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added required option for the attribute fields.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added a new feature to select some predefined terms in taxonomy fields of search builder.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added list / grid switcher in the half map skin.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added random sort option.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added an option to manage zoom levels of GPS feature.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added no listing message.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-improved"><?php esc_html_e('Improved the settings menu.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue of not having HTML codes in Notifications.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in modal content.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in featured image uploading for guest users.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.7.0 <span>July 11th, 2020</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Team', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added an option to load single listing page into light-box on click of marker.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-updated"><?php echo esc_html__('Improved the Listdom icons.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some issues.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.6.3 <span>June 6th, 2020</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some UI issues related to icons.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.6.2 <span>June 5th, 2020</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Rate & Review', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added radius search in search module.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added compatibility with WP 2020 theme.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-updated"><?php esc_html_e('Applied many improvements.', 'listdom'); ?></span>
            <ul class="lsd-sub-changelog">
                <li><?php esc_html_e('Escaped all the translate-able strings based on their context.', 'listdom'); ?></li>
                <li><?php esc_html_e('Improved security by sanitizing inputs.', 'listdom'); ?></li>
                <li><?php esc_html_e('Escaped dynamic HTML tags.', 'listdom'); ?></li>
                <li><?php esc_html_e('Improved security of Embed feature.', 'listdom'); ?></li>
                <li><?php esc_html_e('Fixed some issues in loading CSS and JS assets.', 'listdom'); ?></li>
                <li><?php esc_html_e('Removed inline CSS and JS codes.', 'listdom'); ?></li>
                <li><?php esc_html_e('Improved the structure of Listdom API.', 'listdom'); ?></li>
            </ul>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in assigning listing to user after approving by admin.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in showing map element in style 1.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.6.1 <span>May 10th, 2020</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Labelize', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Memberships', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Paid Member Subscriptions Integration', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added hierarchical dropdown method for taxonomies in search builder.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added some new endpoints to the API.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-updated"><?php esc_html_e('Improved security of Listdom and addons.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in search builder regarding default values.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some issues.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.6.0 <span>March 23rd, 2020</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Claim', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Topup', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added an ability to show all values of a certain attribute in the search builder.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-updated"><?php esc_html_e('Improved security of Listdom.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue regarding showing all attributes in API.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.5.0 <span>February 8th, 2020</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Favorites', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Rank', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added schema (Structured Data) feature to boost SEO.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-updated"><?php esc_html_e('Improved Listdom Restful API.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some issues in permission of Restful API.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in search of text fields.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some issues.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.4.0 <span>January 11th, 2020</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Mobile Application', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added Listdom Restful API.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added embed code feature to submit videos, virtual tours etc. for certain listings!', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed category hierarchy issue on attributes menu.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed a conflict between Listdom and Elementor.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some PHP notices.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.3.1 <span>December 17th, 2019</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('CSV', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added dashboard module controls so the modules can be disabled / enabled.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added ability to export and import listing gallery.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in target page of search form when the shortcode loads in archive instead of singular page.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some PHP notices.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.3.0 <span>December 2nd, 2019</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-addon"><?php echo sprintf(
                /* translators: %s: Add-on name. */
                esc_html__('[ADDON] Released %s addon!', 'listdom'),
                '<strong>'.esc_html__('Advanced Map', 'listdom').'</strong>'
            ); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added advanced import and export system.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in map search feature.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.2.1 <span>November 27th, 2019</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added hierarchical support for category taxonomy.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added an dashboard notification system to manage the system emails.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added HTML marker to leaflet.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-updated"><?php esc_html_e('Improved leaflet clustering for polygon, rectangle and poly-lines.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in leaflet clustering.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.2.0 <span>November 5th, 2019</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added dashboard shortcode to add and manage listings from frontend.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added clustering feature for leaflet map.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added search functionality to the shortcode builder for different skins.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some issues on search builder.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in availability form.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in warning of Google Maps API Key.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in saving the attributes.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed a query issue on skins.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.1.1 <span>October 16th, 2019</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added multiple dropdown search methods.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-updated"><?php esc_html_e('Improved dummy data importer to import a default search form too.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some issues on settings page.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.1.0 <span>October 15th, 2019</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added Advanced Search Builder', 'listdom'); ?></span>
            <ul class="lsd-sub-changelog">
                <li><?php esc_html_e('Ability to create different search forms with different rows, styles and fields.', 'listdom'); ?></li>
                <li><?php esc_html_e('Added price field with options.', 'listdom'); ?></li>
                <li><?php esc_html_e('Added address field.', 'listdom'); ?></li>
                <li><?php esc_html_e('Added dropdown search method.', 'listdom'); ?></li>
                <li><?php esc_html_e('Added radio input search method.', 'listdom'); ?></li>
                <li><?php esc_html_e('Added checkboxes search method.', 'listdom'); ?></li>
                <li><?php esc_html_e('Added text input search method.', 'listdom'); ?></li>
                <li><?php esc_html_e('Added dropdown+ search method.', 'listdom'); ?></li>
                <li><?php esc_html_e('Added Min/Max input search method.', 'listdom'); ?></li>
            </ul>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added ability to hide email, fax, mobile, etc. in owner element.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-updated"><?php esc_html_e('Improved search widget to work with search builder!', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some issues.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.0.2 <span>September 7th, 2019</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-updated"><?php esc_html_e('[PRO] Improved design of GPS icon in map module.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed some tiny issues.', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.0.1 <span>September 2nd, 2019</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-updated"><?php esc_html_e('[PRO] Improved activation and update process!', 'listdom'); ?></span>
        </li>
    </ul>

    <h2>v1.0.0 <span>August 25th, 2019</span></h2>
    <ul class="lsd-changelog">
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added Shortcode Generator.', 'listdom'); ?></span>
            <ul class="lsd-sub-changelog">
                <li><?php esc_html_e('Ability to select shortcode skin from 10 different skins and many styles.', 'listdom'); ?></li>
                <li><?php esc_html_e('Ability to filter listings using category, tag, label, location, feature and author filter options!', 'listdom'); ?></li>
                <li><?php esc_html_e('Ability to select default order options.', 'listdom'); ?></li>
                <li><?php esc_html_e('Ability to disable / enable sort bar and its options!', 'listdom'); ?></li>
                <li><?php esc_html_e('Ability to select map provider per shortcode!', 'listdom'); ?></li>
                <li><?php esc_html_e('Added many configuration options to achieve your desired look and feel!', 'listdom'); ?></li>
            </ul>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added Listing Category.', 'listdom'); ?></span>
            <ul class="lsd-sub-changelog">
                <li><?php esc_html_e('Ability to select an icon for each category. The icon will show in category shortcodes and map markers.', 'listdom'); ?></li>
                <li><?php esc_html_e('Ability to select a color for each category. It use in markers.', 'listdom'); ?></li>
                <li><?php esc_html_e('Ability to select an image for each category. The image will show in category shortcodes.', 'listdom'); ?></li>
                <li><?php esc_html_e('Added ability to filter listings by one or multiple categories in the shortcode generator.', 'listdom'); ?></li>
            </ul>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added Hierarchical Listing Location.', 'listdom'); ?></span>
            <ul class="lsd-sub-changelog">
                <li><?php esc_html_e('Ability to select an image for each location. The image will show in location shortcodes.', 'listdom'); ?></li>
                <li><?php esc_html_e('Added ability to filter listings by one or multiple locations in the shortcode generator.', 'listdom'); ?></li>
            </ul>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added Listing Tags.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added Listing Features.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('Added Listing Labels.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-added"><?php esc_html_e('[PRO] Added Custom Fields.', 'listdom'); ?></span>
            <ul class="lsd-sub-changelog">
                <li><?php esc_html_e('Ability to create personalized fields for listing using attributes feature.', 'listdom'); ?></li>
                <li><?php esc_html_e('Added ability to create attributes per category!', 'listdom'); ?></li>
            </ul>
        </li>
        <li>
            <span class="lsd-changelog-fixed"><?php esc_html_e('Fixed an issue in listing link.', 'listdom'); ?></span>
        </li>
        <li>
            <span class="lsd-changelog-updated"><?php esc_html_e('Improved clustering feature of Google Maps.', 'listdom'); ?></span>
        </li>
    </ul>
</div>
