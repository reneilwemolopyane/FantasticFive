<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Tripistry Landing Page</title>
        <style>
            body{
                margin: 0;
                padding: 0;
                font-family: 'Segoe UI', sans-serif;
            }
            .hero{
                margin-top: 70px;
                height: calc(100vh - 70px);
            }
            .nav-bar{
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 70px;
                background: #1e293b;
                display: flex;
                align-items: center;
                padding: 0 32px;
                box-sizing: border-box;
                z-index: 100;
            }
            nav{
                display: flex;
                align-items: center;
                width: 100%;
            }
            .nav-brand{
                display: flex;
                align-items: center;
                gap: 10px;
                text-decoration: none;
            }
            .nav-logo{
                width: 40px;
                height: 40px;
                object-fit: contain;
            }
            .nav-brand span{
                font-size: 18px;
                font-weight: 700;
                color: #ffffff;
                letter-spacing: 1px;
            }
            .nav-links{
                display: flex;
                gap:32px;
                margin: 0 auto;
            }
            .nav-item{
                text-decoration: none;
                font-size: 15px;
                color: #ffffff;
                font-weight: 500;
                transition: color 0.2s;
            }
            .nav-item:hover, .nav-item.active{
                color: #2dd4bf;
                border-bottom: 2px solid #2dd4bf;
                padding-bottom: 2px;
            }
            .nav-buttons{
                display: flex;
                gap: 12px;
                align-items: center;
            }
            .btn-login{
                text-decoration: none;
                font-size: 14px;
                font-weight: 600;
                color: #ffffff;
                border: 1.5px solid #ffffff;
                padding: 8px 20px;
                border-radius: 6px;
                transition: all 0.2s;
            }
            .btn-login:hover{
                background: #ffffff;
                color: #1e293b;
            }
            .btn-signup{
                text-decoration: none;
                font-size: 14px;
                font-weight: 600;
                color: #ffffff;
                background: #2dd4bf;
                padding: 8px 20px;
                border-radius: 6px;
                transition: background 0.2s;
            }
            .btn-signup:hover{
                background: #14b8a6;
            }
            .hero{
                width: 100%;
                height: 100vh;
                background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('Mountains.jpg');
                background-size: cover;
                background-position: center;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                color: white;
                text-align:center;
                padding: 0 20px;
            }
            .hero h1{
                font-size: 56px;
                font-weight: 800;
                margin: 0 0 16px 0;
            }
            .hero p{
                font-size: 18px;
                margin: 0 0 32px 0;
                opacity: 0.9;
            }
            .search-bar{
                display: flex;
                width: 100%;
                max-width: 500px;
                background: white;
                border-radius: 8px;
                overflow: hidden;
                margin-bottom: 24px;
            }
            .search-bar input{
                flex: 1;
                padding: 14px 20px;
                border: none;
                outline: none;
                font-size: 15px;
                color: #1e293b;
            }
            .search-bar button{
                padding: 14px 20px;
                background: #2dd4bf;
                border: none;
                cursor: pointer;
                font-size: 18px;
            }
            .btn-explore{
                padding: 14px 32px;
                background: #2dd4bf;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
                font-size: 16px;
            }
            .btn-explore:hover{
                background: #14b8a6;
            }
            .destinations-section{
                padding: 60px 80px;
                text-align: center;
                background: #ffffff;
            }
            .destinations-section h2{
                font-size: 28px;
                font-weight: 800;
                color: #0f172a;
                margin: 0 0 8px 0;
                letter-spacing: 1px;
                text-transform: uppercase;
            }
            .destinations-grid{
                display:flex;
                gap: 16px;
                justify-content: center;
                margin-top: 32px;
                flex-wrap: wrap;
            }
            .destination-card{
                position: relative;
                width: 180px;
                height: 220px;
                border-radius: 12px;
                overflow: hidden;
                cursor:pointer;
                flex-shrink: 0;
            }
            .destination-card img{
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.3 ease;
            }
            .destination-card:hover img{
                transform: scale(1.05);
            }
            .dest-label {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                padding: 12px;
                background: linear-gradient(transparent, rgba(0,0,0,0.7));
                color: white;
                text-align: left;
            }
            .dest-label h3 {
               margin: 0;
               font-size: 16px;
               font-weight: 700;
            } 
            .dest-label p {
               margin: 2px 0 0 0;
               font-size: 12px;
               opacity: 0.85;
            }
            .packages-section{
                padding: 60px 80px;
                text-align: center;
                background: #f8fafc;
            }
            .packages-section h2{
                font-size: 28px;
                font-weight: 800;
                color: #0f172a;
                margin: 0 0 8px 0;
                letter-spacing: 1px;
                text-transform:uppercase;
            }
            .packages-grid{
                display: flex;
                gap: 24px;
                justify-content: center;
                margin-top: 32px;
                flex-wrap: wrap;
            }
            .packages-card{
                background: #ffffff;
                border-radius: 12px;
                overflow: hidden;
                width: 300px;
                box-shadow: 0 4px 16px rgba(0,0,0,0.08);
                text-align: left;
                transition: transform 0.2 ease;
            }
            .package-card:hover{
                transform:translate(-4px);
            }
            .package-img-wrapper{
                position:relative;
                height: 180px;
            }
            .package-img-wrapper img{
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .rating-badge {
                position: absolute;
                top: 12px;
                left: 12px;
                background: #2dd4bf;
                color: white;
                font-size: 12px;
                font-weight: 700;
                padding: 4px 10px;
                border-radius: 20px;
            }
            .card-body{
                padding: 14px;
            }
            .card-body h3{
                font-size: 18px;
                font-weight: 700;
                color: #0f172a;
                margin: 0 0 8px 0;
            }
            .card-meta{
                display: flex;
                gap: 16px;
                font-size: 13px;
                color: #64748b;
                margin-bottom: 10px;
            }
            .price{
                font-size: 20px;
                font-weight: 700;
                color: #2563eb;
                margin: 0 0 4px 0;
            }
            .agency{
                font-size: 13px;
                color: #64748b;
                margin: 0 0 12px 0;
            }
            .btn-deal{
                display:block;
                text-align: center;
                padding: 10px;
                border: 1.5px solid #2dd4bf;
                color: #2dd4bf;
                border-radius: 6px;
                text-decoration: none;
                font-weight: 600;
                font-size: 14px;
                transition: all 0.2;
            }
            .btn-deal:hover{
                background:#2dd4bf;
                color: white;
            }
            .view-all-wrapper{
                margin-top: 40px;
            }
            .btn-view-all{
                padding: 14px 40px;
                background: #2dd4bf;
                color: white;
                text-decoration: none;
                font-weight: 600;
                font-size: 14px;
                transition: background 0.2;
            }
            .btn-view-all:hover{
                background: #14b8a6;
            }
            .why-section{
                padding: 60px 80px;
                text-align: center;
                background: #f0f7ff;
            }
            .why-section h2{
                font-size: 28px;
                font-weight: 800;
                color: #0f172a;
                text-transform: uppercase;
                margin: 0 0 40px 0;
                letter-spacing: 1px;
            }
            .why-grid{
                display: flex;
                gap: 32px;
                justify-content: center;
                flex-wrap: wrap;
            }
            .why-card{
                width: 200px;
                text-align: center;
            }
            .why-icon{
                font-size: 36px;
                display: block;
                margin-bottom: 12px;
            }
            .why-card h3 {
               font-size: 16px;
               font-weight: 700;
               color: #0f172a;
               margin: 0 0 8px 0;
            }
            .why-card p {
               font-size: 13px;
               color: #64748b;
               margin: 0;
            }
            .about-section{
                padding: 60px 80px;
                background: #ffffff;
            }
            .about-wrapper{
                display: flex;
                gap: 60px;
                align-items: center;
                max-width:1000px;
                margin: 0 auto;
            }
            .about-img{
                width: 400px;
                flex-shrink: 0;
                border-radius: 12px;
                overflow: hidden;
            }
            .about-img img{
                width: 100%;
                height: 300px;
                object-fit: cover;
            }
            .about-text h2{
                font-size: 28px;
                font-weight: 800;
                color: #0f172a;
                text-transform: uppercase;
                margin: 0 0 16px 0;
                letter-spacing: 1px;
            }
            .about-text p{
                font-size: 15px;
                color: #64748b;
                line-height: 1.7;
                margin: 0 0 16px 0;
            }
            .faq-section{
                padding: 60px 80px;
                background: #f8fafc;
            }
            .faq-section h2{
                font-size: 28px;
                font-weight: 800;
                color: #0f172a;
                text-transform: uppercase;
                text-align: center;
                margin: 0 0 40px 0;
                letter-spacing: 1px;
            }
            .faq-list{
                max-width: 700px;
                margin: 0 auto;
            }
            .faq-item{
                border-bottom: 1px solid #e2e8f0;
                padding: 16px 0;
            }
            .faq-question {
               display: flex;
               justify-content: space-between;
               align-items: center;
               font-size: 15px;
               font-weight: 600;
              color: #1e293b;
                cursor: pointer;
            }
            .faq-answer {
                font-size: 14px;
                color: #64748b;
                margin-top: 10px;
                display: none;
                line-height: 1.6;
            }
            .faq-answer.open{
                display:block;
            }
            .footer{
                background: #0f172a;
                color: #ffffff;
                padding: 60px 80px 20px;
            }
            .footer-grid{
                display: flex;
                gap: 60px;
                flex-wrap: wrap;
                margin-bottom: 40px;
            }
            .footer-brand{
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            .footer-logo{
                width: 40px;
                height: 40px;
                object-fit: contain;
            }
            .footer-brand strong{
                font-size: 16px;
                letter-spacing: 2px;
            }
            .footer-brand p {
               font-size: 13px;
               color: #94a3b8;
               margin: 0;
               line-height: 1.6;
            }
            .footer-col {
               display: flex;
               flex-direction: column;
               gap: 10px;
            }
            .footer-col h4 {
                font-size: 14px;
                font-weight: 700;
                letter-spacing: 1px;
                text-transform: uppercase;
                margin: 0 0 8px 0;
            }
            .footer-col a {
               font-size: 14px;
               color: #94a3b8;
               text-decoration: none;
               transition: color 0.2s;
            }
            .footer-col a:hover {
               color: #ffffff;
            }
            .footer-bottom {
              border-top: 1px solid #1e293b;
              padding-top: 20px;
              text-align: center;
              font-size: 13px;
              color: #64748b;
             }
            .social-icons a img{
                width: 32px;
                height: 32px;
                object-fit: contain;
                border-radius: 8px;
                transition: opacity 0.2s;
            }
            .social-icons a img:hover{
                opacity: 0.7;
            }

        </style>
    </head>
    <body>
        <div class="nav-bar">
            <nav>
                <a href="#" class="nav-brand">
                    <img src="Pictures/Tripistry_logo.jpg" alt="Tripistry Logo" class="nav-logo">
                    <span>Tripistry</span>
                </a>
                <div class="nav-links">
                    <a href="#" class="nav-item active">Home</a>
                    <a href="#packages" class="nav-item">Packages</a>
                    <a href="#about" class="nav-item">About</a>
                    <a href="#about" class="nav-item">FAQ</a>
                </div>
                <div class="nav-buttons">
                    <a href="login.php" class="btn-login">Login</a>
                    <a href="signup.php" class="btn-signup">Signup</a>
                </div>
            </nav>
        </div>
        <div class="hero">
            <h1>Discover Your Next Adventure</h1>
            <p>Compare travel packages from trusted agencies and book with confidence.</p>
            <div class="search-bar">
                <input type="text" placeholder="Search Destination...">
                <button>🔍</button>
            </div>
            <a href="#packages" class="btn-explore">Explore Packages</a>
        </div>
        <section class="destination-sections" id="destination">
            <h2>Popular Destinations</h2>
            <div class="destinations-grid">
                <div class="destination-card">
                    <img src="Pictures/CPT_attraction.jpeg" alt="Cape Town">
                    <div class="dest-label">
                        <h3>Cape Town</h3>
                        <p>📍 South Africa</p>
                    </div>
                </div>
                <div class="destination-card">
                    <img src="Pictures/Paris_attraction.jpeg" alt="Paris">
                    <div class="dest-label">
                        <h3>Paris</h3>
                        <p>📍 France</p>
                    </div>
                </div>
                <div class="destination-card">
                    <img src="Pictures/Bali_attraction.jpeg" alt="Bali">
                    <div class="dest-label">
                        <h3>Bali</h3>
                        <p>📍 Indonesia</p>
                    </div>
                </div>
                <div class="destination-card">
                    <img src="Pictures/Dubai_attraction.jpeg" alt="Dubai">
                    <div class="dest-label">
                        <h3>Dubai</h3>
                        <p>📍 UAE</p>
                    </div>
                </div>
                <div class="destination-card">
                    <img src="Pictures/Zanzibar_attraction.jpeg" alt="Zanzibar">
                    <div class="dest-label">
                        <h3>Zanzibar</h3>
                        <p>📍 Tanzania</p>
                    </div>
                </div>
            </div>
        </section>
        <section class="packages-section" id="packages">
            <h2>Featured Packages</h2>
            <div class="packages-grid">
                <div class="package-card">
                    <div class="package-img-wrapper">
                        <span class="rating-badge">4.8 ⭐</span>
                        <img src="Pictures/Japan_package.jpeg" alt="Japan Escape">
                    </div>
                    <div class="card-body">
                        <h3>Japan Escape</h3>
                        <div class="card-meta">
                            <span>⏱ 5 Days / 4 Nights</span>
                            <span>👥 2 People</span>
                        </div>
                        <p class="price">R15 999</p>
                        <p class="agency">by Wanderlust Travels</p>
                        <a href="package_details.php" class="btn-deal">View Deal</a>
                    </div>
                </div>
                <div class="package-card">
                    <div class="package-img-wrapper">
                        <span class="rating-badge">4.9 ⭐</span>
                        <img src="Pictures/Mauritius_package.jpeg" alt="Mauritius Retreat">
                    </div>
                    <div class="card-body">
                        <h3>Mauritius Retreat</h3>
                        <div class="card-meta">
                            <span>⏱ 7 Days / 6 Nights</span>
                            <span>👥 2 People</span>
                        </div>
                        <p class="price">R18 500</p>
                        <p class="agency">by Oceanic Holidays</p>
                        <a href="package_details.php" class="btn-deal">View Deal</a>
                    </div>
                </div>
                <div class="package-card">
                    <div class="package-img-wrapper">
                        <span class="rating-badge">4.7 ⭐</span>
                        <img src="Pictures/Safari_package.jpeg" alt="Safari Adventure">
                    </div>
                    <div class="card-body">
                        <h3>Safari Adventure</h3>
                        <div class="card-meta">
                            <span>⏱ 10 Days / 9 Nights</span>
                            <span>👥 2 People</span>
                        </div>
                        <p class="price">R22 000</p>
                        <p class="agency">by Wild Horizons</p>
                        <a href="package_details.php" class="btn-deal">View Deal</a>
                    </div>
                </div>

            </div>
            <div class="view-all-wrapper">
                <a href="package_details.php" class="btn-view-all">View All Packages</a>
            </div>
        </section>
        <section class="why-section">
            <h2>Why Choose Tripistry</h2>
            <div class="why-grid">
                <div class="why-card">
                    <span class="why-icon">🏷️</span>
                    <h3>Compare Prices</h3>
                    <p>Find the best deals from multiple agencies.</p>
                </div>
                <div class="why-card">
                    <span class="why-icon">✅</span>
                    <h3>Verified Agencies</h3>
                    <p>All agencies are verified and trusted.</p>
                </div>
                <div class="why-card">
                    <span class="why-icon">👥</span>
                    <h3>Group Travel</h3>
                    <p>Join group trips or travel with friends.</p>
                </div>
                <div class="why-card">
                    <span class="why-icon">⭐</span>
                    <h3>Trusted Reviews</h3>
                    <p>Real reviews from real travellers.</p>
                </div>
            </div>
        </section>
        <section class="about-section" id="about">
            <div class="about-wrapper">
                <div class="about-img">
                    <img src="Pictures/About.jpeg" alt="About Tripistry">
                </div>
                <div class="about-text">
                    <h2>About Us</h2>
                    <p>Triptistry connects travellers with trusted travel agencies to compare curated packages, explore new destinations, and create unforgettable memories.</p>
                    <p>Our mission is to make travel planning simple, transparent and accessible for everyone.</p>
                </div>
                </div>
            </div>
        </section>
        <section class="faq-section" id="faq">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        How does booking work?
                        <span class="faq-icon">▼</span>
                    </div>
                    <div class="faq-answer">Browse packages, select one that suits you.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        Can solo travellers join group trips?
                        <span class="faq-icon">▼</span>
                    </div>
                    <div class="faq-answer">Yes! Group trips are open to solo travellers looking to meet new people and share experiences.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        Are agencies verified?
                        <span class="faq-icon">▼</span>
                    </div>
                    <div class="faq-answer">All agencies on Triptistry go through a verification process before listing packages.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        Can i cancel my booking?
                        <span class="faq-icon">▼</span>
                    </div>
                    <div class="faq-answer">Cancellation policies vary by agency. Check the package details for the specific policy.</div>
                </div>
            </div>
        </section>
        <footer class="footer">
            <div class="footer-grid">
                <div class="footer-brand">
                    <img src="Pictures/Tripistry_logo.jpg" alt="Logo" class="footer-logo">
                    <strong>TRIPISTRY</strong>
                    <p>Your journey.<br>Our passion.</p>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <a href="#">Home</a>
                    <a href="#packages">Packages</a>
                    <a href="#about">About</a>
                    <a href="#faq">FAQ</a>
                </div>
                <div class="footer-col">
                    <h4>Support</h4>
                    <a href="#">Contact Us</a>
                    <a href="#">Terms & Conditions</a>
                    <a href="#">Privacy Policy</a>
                </div>
                <div class="social-icons">
                    <a href="#"><img src="Pictures/Instagram.png" alt="Instagram"></a>
                    <a href="#"><img src="Pictures/Tiktok.png" alt="Tiktok"></a>
                    <a href="#"><img src="Pictures/X.png" alt="X"></a>
                </div>
            </div>
            <script>
                function toggleFaq(el){
                    const answer = el.nextElementSibling;
                    const icon = el.querySelector('.faq-icon');
                    answer.classList.toggle('open');
                    icon.textContent = answer.classList.contains('open') ? '▲' : '▼';
                }
            </script>
        </footer>
    </body>

</html>