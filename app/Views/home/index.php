<section class="hero-section">
    <div class="hero-grid">
        <div class="hero-copy">
            <span class="hero-badge">Yayi Youth Vanguard</span>
            <h1 class="hero-title">Membership and Executives Registration Portal.</h1>
            <p class="hero-text">
                Registration of all categories of members and Polling Unit Marshal integration.
            </p>
            <div class="hero-actions">
                <a class="btn btn--accent" href="<?= url('/register'); ?>">Start Registration</a>
                <a class="btn btn--ghost" href="<?= url('/login'); ?>">Login</a>
            </div>
        </div>
        <div class="hero-panel">
            <div class="hero-panel__card">
                <div class="hero-panel__image">
                    <img
                        class="hero-panel__logo"
                        src="<?= htmlspecialchars(url('assets/' . rawurlencode('WhatsApp Image 2026-07-23 at 1.17.59 PM.jpeg')), ENT_QUOTES, 'UTF-8'); ?>"
                        alt="Yayi Youth Vanguard logo"
                    >
                </div>
                <div class="hero-panel__content">
                    <h2>Membership-Ready Dashboards</h2>
                    <p>QR membership cards, Polling Unit Marshal appointment, analytics, and export tools.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="section-heading">
    <div class="section-heading__eyebrow">Explore our services</div>
    <h2>Use one secure portal for registration and administration</h2>
    <p>Use the public site for entry points and the dashboard for operational modules once you are signed in.</p>
</div>

<section class="service-grid">
    <article class="service-card">
        <div class="service-card__icon"><i class="fa-solid fa-id-card"></i></div>
        <h3>Membership</h3>
        <p>Register members, generate membership numbers, produce QR codes, and issue printable cards.</p>
    </article>
    <article class="service-card">
        <div class="service-card__icon"><i class="fa-solid fa-users-gear"></i></div>
        <h3>Executives</h3>
        <p>Manage state, senatorial, LGA, and ward executives in a single organized hierarchy.</p>
    </article>
    <article class="service-card">
        <div class="service-card__icon"><i class="fa-solid fa-vote-yea"></i></div>
        <h3>Marshal Monitoring</h3>
        <p>Track marshal assignments, activity status, uploads, and submission time from one view.</p>
    </article>
    <article class="service-card">
        <div class="service-card__icon"><i class="fa-solid fa-chart-column"></i></div>
        <h3>Reports</h3>
        <p>Generate secure reports, export to PDF or Excel, and review audit trails for all key actions.</p>
    </article>
</section>

<section class="info-band" id="about-platform">
    <div class="info-band__inner">
        <div class="info-panel">
            <span class="info-label">About the platform</span>
            <h3>Structured for statewide membership and marshal control.</h3>
            <p>
                This portal organizes members, polling units, wards, LGAs, executives, and marshal workflows in a clean administrative flow.
                It is designed to stay discreet while remaining focused on internal operations.
            </p>
            <p class="mb-0">
                Use the dashboard after login for the operational modules, and keep the public site minimal for first-time visitors.
            </p>
        </div>
        <div class="info-panel info-panel--green">
            <span class="info-label">Live status</span>
            <h3>Ready for approvals and publication.</h3>
            <p>
                The system is prepared for phased workflows such as submitted, pending, verified, approved, and published records.
            </p>
            <div class="info-meta">
                <div>
                    <strong>20</strong>
                    <span>LGAs</span>
                </div>
                <div>
                    <strong>3</strong>
                    <span>Senatorial districts</span>
                </div>
                <div>
                    <strong>100%</strong>
                    <span>Responsive layout</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-banner">
    <div class="cta-banner__inner">
        <div class="cta-banner__copy">
            <h2>Manage registration from intake to published records.</h2>
            <p>Built for admins, executives, and polling marshals with secure workflows and a dashboard-first experience.</p>
        </div>
        <div class="cta-banner__action">
            <a class="btn btn-light" href="<?= url('/register'); ?>">Join the platform</a>
        </div>
    </div>
</section>

<section class="news-section">
    <div class="section-heading" style="padding:0;">
        <div class="section-heading__eyebrow">News</div>
        <h2>Updates and announcements</h2>
    </div>
    <div class="news-grid">
        <article class="news-feature">
            <div class="news-feature__media"></div>
            <div class="news-feature__body">
                <p>Catch up with updates and notices happening across the organization. This space can later connect to a real CMS or database-driven news feed.</p>
            </div>
        </article>
        <div class="news-list">
            <article class="news-item">
                <h3>Marshal onboarding training starts across the state</h3>
                <p>Polling marshals and ward executives will get access to appointment and verification workflows in the next release.</p>
                <div class="news-item__meta"><span>Posted today</span><span>Read more</span></div>
            </article>
            <article class="news-item">
                <h3>Membership registration now supports QR cards</h3>
                <p>Members will receive a membership number, QR code, and downloadable card after approval.</p>
                <div class="news-item__meta"><span>Posted today</span><span>Read more</span></div>
            </article>
            <article class="news-item">
                <h3>Membership dashboard is being prepared for live use</h3>
                <p>Charts, filters, and status-based approval flows are ready to be connected to the database.</p>
                <div class="news-item__meta"><span>Posted today</span><span>Read more</span></div>
            </article>
        </div>
    </div>
</section>

