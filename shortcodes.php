<?php
namespace WWOPN_PRs;

function shortcode_media_contact_name() {
    if (\get_option('wpn_prs_mediacontact')) {
        $contact = \get_option('wpn_prs_mediacontact');
        return \esc_html($contact['mc_name']);
    }
    return '';
};
\add_shortcode('media-contact-name', __NAMESPACE__ . '\shortcode_media_contact_name');

function shortcode_media_contact_email() {
    if (\get_option('wpn_prs_mediacontact')) {
        $contact = \get_option('wpn_prs_mediacontact');
        return \esc_html($contact['mc_email']);
    }
    return '';
};
\add_shortcode('media-contact-email', __NAMESPACE__ . '\shortcode_media_contact_email');

function shortcode_media_contact_twitter() {
    if (\get_option('wpn_prs_mediacontact')) {
        $contact = \get_option('wpn_prs_mediacontact');
        return \esc_html($contact['mc_twitter']);
    }
    return '';
};
\add_shortcode('media-contact-twitter', __NAMESPACE__ . '\shortcode_media_contact_twitter');

