EARG Homepage V1
================

Files included:
- index.html
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
- Alpine.js is loaded by CDN in index.html.
- The hero slider changes every 30 seconds and has manual left/right controls.
- Newsletter form is wired to newsletter.php which saves emails to the newsletter_subscribers table.
- The contact form on about.html posts to contact.php which saves messages to the contact_messages table.
- Founder's Story public page (founder-story.php) is edited in the admin area (admin/founder_story.php); content is stored in the founder_story table.
- Admin messages inbox (admin/messages.php) lists contact form messages with mark read / archive / delete actions.
- Admin subscribers list (admin/subscribers.php) shows newsletter signups and can download them as a CSV.
- Social links and secondary page links are placeholders and should be connected as pages are built.
