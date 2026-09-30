# Ideas for Improving the Website

This file lists features and fixes I think would make the EA Research Group website better. Each idea is explained in simple English, with a rough **priority** and **effort** (Small / Medium / Large) so you can decide what to tackle first.

---

## Part 1 — Fix What Is Already Broken (do these first)

These aren't new features. They are broken or unfinished pieces that are already on the site. Fixing them makes everything else look professional.

| # | What is wrong | Why it matters | Effort |
|---|---------------|----------------|--------|
| 1 | The **contact form** on the About page doesn't really work. It just opens the visitor's email program (`mailto:`). There is already a database table called `contact_messages` that is never used. | People send you messages and you never receive them. A form that does nothing looks bad. | Medium |
| 2 | The **newsletter sign-up box** on the homepage only pops up a fake "thanks" message. The email is not saved anywhere. | You are collecting emails and losing them. | Medium |
| 3 | The **Events page** is a blank page that just says "content will come later". | Visitors click "Events" and find nothing. Better to hide it or fill it. | Medium |
| 4 | The **admin dashboard** always shows "Publications: 0", even when there are publications. The code was started but never finished. | The owner will think the website is empty. | Small |
| 5 | Some buttons/links are "dead": "Learn More", "Work With Us", and the logo image is missing on a few pages (the file `earg-logo.png` doesn't exist). | Dead links and missing images look unfinished. | Small |
| 6 | The page called `programs.html` has the wrong title ("Publications") and repeats the Publications page. It is also listed in the menu and the search engine map. | Confusing for visitors and bad for search engines. | Small |
| 7 | The homepage photos are very large (some over 10 MB each). | The homepage loads slowly, especially on phones. | Small–Medium |

---

## Part 2 — New Features That Give the Most Value

These are the features most likely to help the organization grow and look professional.

### Feature A — Real Contact Form + Admin Inbox

The database already has a table for messages. The missing piece is doing something with it.

- On the **About → Contact** section, submit messages to the website database instead of the email program.
- Add an **Inbox** page in the Admin Panel where the owner can read, mark as read, archive, and reply to messages.
- Send the owner an email (or a simple notification) whenever a new message arrives.

**Benefit:** You actually get and manage the messages people send you.
**Effort:** Medium.

---

### Feature B — Real Newsletter Sign-up + Email List

Right now the newsletter box does nothing. Two options:

- **Option 1 (simple):** Save emails in a database table and add an "Email List" page in Admin to download them (e.g. as a spreadsheet). Use the list later with any email service.
- **Option 2 (powerful):** Connect to a service like Mailchimp or Brevo, which sends the emails for you and gives you a professional sign-up.

**Benefit:** You start building your own mailing list.
**Effort:** Medium (Option 1) or Medium–Large (Option 2).

---

### Feature C — Events Page (real one)

Build an Events section just like the Opportunities section already works:

- A new `events` table in the database (title, date, time, place, description, link).
- An **Events** page that lists upcoming events from the database.
- A small **Admin** section to add, edit, and remove events.
- Show past events in a separate "Past Events" list.

**Benefit:** A real, always-up-to-date Events page that replaces the empty one.
**Effort:** Large (but it reuses the same patterns as Opportunities, so it's mostly copy-work).

---

### Feature D — Make the Opportunities List Easier to Browse

The Opportunities list currently shows everything at once. Visitors often want to narrow it down:

- **Filter buttons:** by type (Scholarship, Fellowship, Grant…), by country, and by status (Open / Closing Soon / Closed).
- **A search box** to type keywords.
- **Pagination** ("Page 1 2 3…") so long lists load faster.

**Benefit:** It's much easier to find a relevant opportunity.
**Effort:** Medium.

---

### Feature E — Featured / Important Items on the Homepage

The homepage has an empty section for the hero slider, and the "Latest Updates" cards are hardcoded. Make them dynamic:

- Show the **latest 3 opportunities** and **latest 3 publications** automatically (pulled from the database) instead of static text.
- Add short headlines and a "See all" button to the big homepage slider.

**Benefit:** The homepage always looks fresh without editing HTML by hand.
**Effort:** Medium.

---

## Part 3 — Very Useful Extras

### Feature F — Programme / About-Us Pages Instead of Links to Nowhere

"Work With Us" and "Our Partners" are in the menu but go nowhere. Options:

- Build a simple **Work With Us** page (what you're looking for, how to apply).
- Build an **Our Partners** page (logos + short text).
- Add an **Advisory Board** section on the About page (it's already written in the code but switched off, with "Advisor Name" placeholders — finish it with your real advisors).

**Effort:** Small–Medium.

---

### Feature G — Team Member Pages (Profiles)

Currently each board/team member is a photo with a name. Add:

- A clickable **bio page** for each person (background, interests, email).
- Or a small "read more" popup on the About page.

**Best with admin control:** a `team_members` table + a simple Admin editor, so you can update people without touching the code.
**Effort:** Medium–Large.

---

### Feature H — Search Feature (site-wide)

Add a search box that looks through opportunities, publications, and events at once.

**Effort:** Medium.

---

### Feature I — "Share" and "Print" Buttons on Article Pages

On each opportunity or publication page, add:

- A **share button** (copy link, or share to WhatsApp/Facebook — very useful in Ghana since WhatsApp is popular).
- A **print / save as PDF** button.

**Effort:** Small.

---

### Feature J — Last Updated / Real Dates on Opportunities

Opportunities can be stale. Show:

- How long ago it was posted ("Posted 3 weeks ago").
- Automatically mark an opportunity **"Closed"** when its deadline has passed, or show warning "Closing soon" when the deadline is near.

**Effort:** Small–Medium.

---

### Feature K — Newsletter Archive

Turn newsletters into a proper archive:

- The Newsletter section under Publications already can hold content — just add a "Subscribe to this newsletter" button at the end of each one.
- Allow visitors to read older newsletters in one place.

**Effort:** Small.

---

### Feature L — Google Analytics / Visitor Tracking

Add the free Google Analytics code so you can see:

- How many people visit, which pages they read, and where they come from.
- Which opportunities get the most clicks.

**Effort:** Small.

---

## Part 4 — Security & Housekeeping (recommended for development, not visitors)

| # | Item | Why |
|---|------|-----|
| 1 | **Role-based access** — the system knows about "Super Admin" and "Editor" but never uses it. Add permissions so an Editor can manage opportunities but not delete the site. | So you can safely give someone else limited access. |
| 2 | **Locked-down admin login** — add a "Forgot password" reset and, later, two-factor login. | Protects your admin account. |
| 3 | **Faster images** — squeeze the large homepage/hero photos so pages load quickly. | Keeps visitors on the site. |
| 4 | **Clean up the code** — remove the duplicate/unused files (`programs.html`, duplicate Emma photos, old placeholder pages) or keep them in a list of "known to-dos". | Keeps the repo tidy so future work is easier. |

---

## Suggested Order of Work (if I had to pick)

1. **Fix Phase:** Part 1 items 1, 2, 4, 5, 6 (contact form, newsletter, dashboard bug, dead links, page titles). These are quick wins.
2. **Value Phase (first half):** Feature A (Contact/Inbox), Feature C (Events), Feature E (dynamic homepage).
3. **Value Phase (second half):** Feature B (newsletter backend), Feature D (filters/search on Opportunities).
4. **Polish Phase:** The rest, as time allows.

---

## Question for You

The most important question is: **do you want to manage the site all by yourself, or hand parts of it to other people?**

- **If all by yourself** → focus on Features A, C, E and the fixes first.
- **If you'll have helpers** → move Part 4 item 1 (roles/permissions) higher up the list.

I can implement any of these — just tell me which feature to build first.