EARG Homepage V1
================

Files included:
- index.php
- assets/css/index.css
- assets/js/index.js
- assets/images/earg-logo.png
- assets/images/earg-logo-transparent.png
- assets/images/slide-mentorship.svg
- assets/images/slide-research.svg
- assets/images/slide-community.svg
- assets/documents/earg-constitution-working-reference.txt

Notes:
- The homepage uses separate CSS and JS files, not all-in-one HTML.
- All public pages are PHP pages. The header/navigation and footer are shared
  from ONE place so a single change updates every page:
  - includes/navbar.php (logo + main navigation)
  - includes/footer.php (quick links, contact and social links)
  - includes/site.php (url() helper; builds links that work from any folder)
- Alpine.js is loaded by CDN in index.php.
- The hero slider changes every 30 seconds and has manual left/right controls.
- Newsletter form is wired to newsletter.php which saves emails to the newsletter_subscribers table.
- The About page is split into standalone pages under views/About/:
  - who-we-are.php, governing-board.php, our-team.php, contact-us.php, founder-story.php
  - The old about.php and founder-story.php are kept as redirects for old links.
  - contact-us.php uses Bootstrap's grid for the layout and the site's existing .contact-* styles (about.css) for the look; assets/css/contact.css holds the grid glue.
- The contact form on views/About/contact-us.php posts to contact.php which saves messages to the contact_messages table.
- Founder's Story public page (views/About/founder-story.php) is edited in the admin area (admin/founder_story.php); content is stored in the founder_story table.
- Admin pages share their own navigation from admin/includes/admin_navbar.php.
- Admin messages inbox (admin/messages.php) lists contact form messages with mark read / archive / delete actions.
- Admin subscribers list (admin/subscribers.php) shows newsletter signups and can download them as a CSV.
- Social links and secondary page links are placeholders and should be connected as pages are built.
