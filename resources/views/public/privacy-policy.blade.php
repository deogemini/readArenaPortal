<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ReadArena's Privacy Policy explains what information we collect, how we use it, and the privacy choices available to readers and authors.">
    <title>Privacy Policy | ReadArena</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4EBD8] text-[#24150D] antialiased">
@include('components.language-switcher')
<div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(216,168,62,0.16),_transparent_38%)]">
    <header class="border-b border-[#d8c9ad] bg-[#FBF6EA]/95">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="font-serif text-2xl font-semibold text-[#1B0D05]">ReadArena</a>
            <nav aria-label="Main navigation" class="flex flex-wrap items-center justify-end gap-2 text-sm">
                <a href="{{ route('home') }}" class="rounded-full px-3 py-2 text-[#5e544d] transition hover:bg-[#f1e7d4] hover:text-[#1B0D05]">Home</a>
                <a href="{{ route('library') }}" class="rounded-full px-3 py-2 text-[#5e544d] transition hover:bg-[#f1e7d4] hover:text-[#1B0D05]">Library</a>
                <a href="{{ route('about') }}" class="rounded-full px-3 py-2 text-[#5e544d] transition hover:bg-[#f1e7d4] hover:text-[#1B0D05]">About</a>
                <a href="{{ route('register') }}" class="rounded-full bg-[#1B0D05] px-4 py-2 text-[#FBF6EA] transition hover:bg-[#432919]">Join</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-medium text-[#786A5D] transition hover:text-[#1B0D05]"><span aria-hidden="true">←</span> Back to ReadArena</a>

        <section class="mt-5 overflow-hidden rounded-[28px] border border-[#dfcfad] bg-[#FBF6EA] shadow-[0_16px_50px_rgba(60,37,17,0.08)]">
            <div class="grid gap-8 bg-[linear-gradient(120deg,rgba(251,246,234,0.95),rgba(244,235,216,0.88))] p-6 sm:p-9 lg:grid-cols-[minmax(0,1fr)_19rem] lg:items-end lg:p-12">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#A77920]">Your information, clearly explained</p>
                    <h1 class="mt-3 font-serif text-4xl leading-tight text-[#1B0D05] sm:text-5xl">Privacy Policy</h1>
                    <p class="mt-4 max-w-3xl text-base leading-7 text-[#5e544d]">Clear information about the data ReadArena uses and the choices available to you.</p>
                </div>
                <div class="rounded-2xl border border-[#e2d5bd] bg-[#FBF6EA] p-4">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-[#897a67]">Last updated</p>
                    <p class="mt-1 font-semibold text-[#342319]">October 6, 2026</p>
                    <a href="mailto:info@eportsolutions.co.tz" class="mt-3 inline-flex text-sm font-semibold text-[#6c4b11] underline decoration-[#c6a566] underline-offset-4">Contact us about privacy</a>
                </div>
            </div>
            <div class="border-t border-[#e5d8bf] bg-[#f5eddf] px-6 py-5 sm:px-9 lg:px-12">
                <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-[#A77920]">Privacy at a glance</p>
                <p class="mt-1 max-w-5xl text-sm leading-6 text-[#5e544d]">ReadArena uses your information to provide reading and competition features, keep accounts secure, and send service notifications. Some profile details and content are public by design; quiz results on book pages are shown as aggregates.</p>
            </div>
        </section>

        <div class="mt-6 grid gap-6 lg:grid-cols-[15rem_minmax(0,1fr)] lg:items-start">
            <aside class="rounded-[22px] border border-[#dfcfad] bg-[#FBF6EA] p-5 lg:sticky lg:top-6" aria-label="Privacy policy contents">
                <h2 class="font-serif text-xl text-[#1B0D05]">On this page</h2>
                <nav class="mt-3 grid grid-cols-2 gap-1 text-sm lg:grid-cols-1">
                    <a href="#who-we-are" class="rounded-lg px-2.5 py-2 text-[#5e544d] hover:bg-[#f5eddf]">Who we are</a>
                    <a href="#information" class="rounded-lg px-2.5 py-2 text-[#5e544d] hover:bg-[#f5eddf]">Information we collect</a>
                    <a href="#use" class="rounded-lg px-2.5 py-2 text-[#5e544d] hover:bg-[#f5eddf]">How we use information</a>
                    <a href="#sharing" class="rounded-lg px-2.5 py-2 text-[#5e544d] hover:bg-[#f5eddf]">Sharing information</a>
                    <a href="#visibility" class="rounded-lg px-2.5 py-2 text-[#5e544d] hover:bg-[#f5eddf]">What is public</a>
                    <a href="#cookies" class="rounded-lg px-2.5 py-2 text-[#5e544d] hover:bg-[#f5eddf]">Cookies and app data</a>
                    <a href="#transfers" class="rounded-lg px-2.5 py-2 text-[#5e544d] hover:bg-[#f5eddf]">International processing</a>
                    <a href="#retention" class="rounded-lg px-2.5 py-2 text-[#5e544d] hover:bg-[#f5eddf]">Retention and deletion</a>
                    <a href="#security" class="rounded-lg px-2.5 py-2 text-[#5e544d] hover:bg-[#f5eddf]">Security</a>
                    <a href="#rights" class="rounded-lg px-2.5 py-2 text-[#5e544d] hover:bg-[#f5eddf]">Your choices and rights</a>
                    <a href="#automated-scoring" class="rounded-lg px-2.5 py-2 text-[#5e544d] hover:bg-[#f5eddf]">Quiz scoring</a>
                    <a href="#changes-contact" class="rounded-lg px-2.5 py-2 text-[#5e544d] hover:bg-[#f5eddf]">Changes and contact</a>
                </nav>
            </aside>

            <article class="min-w-0 space-y-4">
                <section id="who-we-are" class="scroll-mt-6 rounded-[22px] border border-[#dfcfad] bg-[#FBF6EA] p-5 sm:p-7">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">01</p>
                    <h2 class="mt-1 font-serif text-2xl text-[#1B0D05]">Who we are</h2>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">ReadArena operates the ReadArena website, Android application, and APIs. This policy explains how we handle personal data when you visit, create an account, use the service, or contact us.</p>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">For privacy questions or requests about your personal data, contact us at <a href="mailto:info@eportsolutions.co.tz" class="font-semibold text-[#6c4b11] underline decoration-[#c6a566] underline-offset-4">info@eportsolutions.co.tz</a>.</p>
                    <p class="mt-3 text-xs leading-6 text-[#786A5D]">We may ask you to confirm your identity before providing account information or completing a privacy request.</p>
                </section>

                <section id="information" class="scroll-mt-6 rounded-[22px] border border-[#dfcfad] bg-[#FBF6EA] p-5 sm:p-7">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">02</p>
                    <h2 class="mt-1 font-serif text-2xl text-[#1B0D05]">Information we collect</h2>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">Account information — name, email address, optional username and phone number, account role, selected language, profile photo, and password credential. Passwords are stored in hashed form, not as readable text.</p>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">If you sign in with Google, Google provides the name and email information needed to create or access your ReadArena account.</p>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">Reading and participation — books on your shelf, reading goals and page progress, bookmarks, quiz answers and scores, leaderboard results, duel invitations and results, live-show applications, and reviews, lessons, recommendations, or improvement ideas you submit.</p>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">Attachments included with an improvement idea are stored so authorized staff can review that submission.</p>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">Device and service data — app platform, push-notification token and device label, online or last-seen status, notification preferences, in-app notifications, and essential session and security records. Website or hosting logs may also record request details such as IP address, browser, device, and event time.</p>
                </section>

                <section id="use" class="scroll-mt-6 rounded-[22px] border border-[#dfcfad] bg-[#FBF6EA] p-5 sm:p-7">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">03</p>
                    <h2 class="mt-1 font-serif text-2xl text-[#1B0D05]">How we use information</h2>
                    <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-7 text-[#5e544d]">
                        <li>Provide, maintain, and secure the website, Android application, and APIs.</li>
                        <li>Run your library, reading progress, goals, quizzes, rankings, duels, live-show participation, and profile.</li>
                        <li>Review submissions, moderate public content, respond to support requests, and improve ReadArena features.</li>
                        <li>Send account, security, quiz, duel, and service notifications by email, SMS, or push when those channels are configured.</li>
                        <li>Prevent fraud, abuse, and technical problems, and meet applicable legal obligations.</li>
                    </ul>
                </section>

                <section id="sharing" class="scroll-mt-6 rounded-[22px] border border-[#dfcfad] bg-[#FBF6EA] p-5 sm:p-7">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">04</p>
                    <h2 class="mt-1 font-serif text-2xl text-[#1B0D05]">When information is shared</h2>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">We do not sell personal data. We share information with service providers that support hosting and storage, email delivery, the configured SMS gateway, Firebase Cloud Messaging push delivery, and Google sign-in when you choose it. They receive information needed to provide the relevant feature.</p>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">Authorized ReadArena staff may access account information when administering the service, reviewing applications or ideas, moderating content, and helping readers.</p>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">We may disclose information when required by law or when needed to protect users, ReadArena, or legal rights.</p>
                </section>

                <section id="visibility" class="scroll-mt-6 rounded-[22px] border border-[#dfcfad] bg-[#FBF6EA] p-5 sm:p-7">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">05</p>
                    <h2 class="mt-1 font-serif text-2xl text-[#1B0D05]">What other readers can see</h2>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">Your display name or username, profile photo, and ranking details may appear in public leaderboards or reader discovery. Other readers may see your online indicator when they browse readers for a duel.</p>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">Reviews, lessons, and recommendations are shown publicly when they are approved, published, and marked public; they may appear with your name.</p>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">Book pages show quiz participation and graded performance as group totals such as reader counts, attempts, averages, and high scores. They do not identify individual quiz takers.</p>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">Improvement ideas and live-show applications are not displayed as public reader content, but authorized staff can access them to manage the service. Avoid putting private or sensitive details in content you choose to publish.</p>
                </section>

                <section id="cookies" class="scroll-mt-6 rounded-[22px] border border-[#dfcfad] bg-[#FBF6EA] p-5 sm:p-7">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">06</p>
                    <h2 class="mt-1 font-serif text-2xl text-[#1B0D05]">Cookies and app data</h2>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">The website uses essential session and security cookies to keep you signed in, protect form submissions, and maintain session settings such as your language choice. Blocking these cookies may prevent sign-in or other features from working.</p>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">The Android application uses authentication and device-token data to connect your account to the service and deliver push notifications. You can manage notification permissions in your device settings.</p>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">When you use Google sign-in or load content hosted by another provider, that provider may receive technical information from your device under its own privacy notice.</p>
                </section>

                <section id="transfers" class="scroll-mt-6 rounded-[22px] border border-[#dfcfad] bg-[#FBF6EA] p-5 sm:p-7">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">07</p>
                    <h2 class="mt-1 font-serif text-2xl text-[#1B0D05]">International processing</h2>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">Some providers that support ReadArena may process information on servers outside Tanzania. Where personal data is transferred across borders, applicable legal requirements and safeguards apply.</p>
                </section>

                <section id="retention" class="scroll-mt-6 rounded-[22px] border border-[#dfcfad] bg-[#FBF6EA] p-5 sm:p-7">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">08</p>
                    <h2 class="mt-1 font-serif text-2xl text-[#1B0D05]">Retention and account deletion</h2>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">We keep account information while your account is active and as needed to provide the service. You can update your profile or delete your account from Profile settings in the website; the Android application also provides account deletion through its account API.</p>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">Account deletion removes the account and associated service records, profile photo, private idea attachments, notifications, and API tokens. Limited information may be retained where needed for legal obligations, security, dispute resolution, or backups and technical logs until their normal removal.</p>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">You can also email us to request access to, correction of, or deletion of your information.</p>
                </section>

                <section id="security" class="scroll-mt-6 rounded-[22px] border border-[#dfcfad] bg-[#FBF6EA] p-5 sm:p-7">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">09</p>
                    <h2 class="mt-1 font-serif text-2xl text-[#1B0D05]">Security</h2>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">We use safeguards such as password hashing, account access controls, and restricted handling of service credentials. No internet service can guarantee absolute security, so please use a unique password and contact us if you suspect unauthorized access to your account.</p>
                </section>

                <section id="rights" class="scroll-mt-6 rounded-[22px] border border-[#dfcfad] bg-[#FBF6EA] p-5 sm:p-7">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">10</p>
                    <h2 class="mt-1 font-serif text-2xl text-[#1B0D05]">Your choices and privacy rights</h2>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">Depending on applicable law and the circumstances, you may have rights to be informed about, access, correct, or request deletion of personal data; restrict or object to certain processing; request a copy or transfer of data; and withdraw consent where processing relies on consent.</p>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">You may ask us a privacy question or make a request by emailing <a href="mailto:info@eportsolutions.co.tz" class="font-semibold text-[#6c4b11] underline decoration-[#c6a566] underline-offset-4">info@eportsolutions.co.tz</a>. You may also contact Tanzania’s Personal Data Protection Commission (PDPC) about your rights or a complaint.</p>
                    <a href="https://pdpc.go.tz/data-subject-rights/" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex rounded-full border border-[#cbb992] px-4 py-2 text-sm font-semibold text-[#5c3b08] transition hover:bg-[#f5eddf]">View PDPC data subject rights <span class="ml-2" aria-hidden="true">↗</span></a>
                </section>

                <section id="automated-scoring" class="scroll-mt-6 rounded-[22px] border border-[#dfcfad] bg-[#FBF6EA] p-5 sm:p-7">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">11</p>
                    <h2 class="mt-1 font-serif text-2xl text-[#1B0D05]">Quiz scoring and rankings</h2>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">Objective quiz answers and leaderboard positions are calculated using the quiz rules and activity recorded by the service. Written responses that require review are sent to an authorized administrator before a final score is set. Contact us if you believe a score or ranking is incorrect.</p>
                </section>

                <section id="changes-contact" class="scroll-mt-6 rounded-[22px] border border-[#dfcfad] bg-[#FBF6EA] p-5 sm:p-7">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#A77920]">12</p>
                    <h2 class="mt-1 font-serif text-2xl text-[#1B0D05]">Changes and contact</h2>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">We may update this policy when the service or its data practices change. The latest version and update date will be posted on this page.</p>
                    <p class="mt-3 text-sm leading-7 text-[#5e544d]">For privacy requests or questions, email <a href="mailto:info@eportsolutions.co.tz" class="font-semibold text-[#6c4b11] underline decoration-[#c6a566] underline-offset-4">info@eportsolutions.co.tz</a>.</p>
                </section>
            </article>
        </div>

        <footer class="mt-8 flex flex-col gap-3 border-t border-[#d8c9ad] py-6 text-sm text-[#786A5D] sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('home') }}" class="font-serif text-lg font-semibold text-[#1B0D05]">ReadArena</a>
            <p>Privacy questions: <a href="mailto:info@eportsolutions.co.tz" class="font-semibold text-[#6c4b11] underline underline-offset-4">info@eportsolutions.co.tz</a></p>
        </footer>
    </main>
</div>
</body>
</html>
