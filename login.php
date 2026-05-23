 <!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Tripistry Login Page</title>
        <style>
            html, body{
                height: 100%;
                margin: 0;
                padding: 0;
                font-family: 'Segoe UI', sans-serif;
                background: #f0f2f6;
                box-sizing: border-box;
            }
            .container{
                display: flex;
                width: 100%;
                height: 100vh;
                position: relative;
            }
            .logo{
                position: absolute;
                top: 20px;
                right: 20px;
                width: 130px;
                height: auto;
                z-index: 10;
                mix-blend-mode: multiply;
            }
            .left-panel{
                width:40%;
                position: relative;
                min-height: 100vh;
                background-image: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.4)), url('login.jpg');
                background-repeat: no-repeat;
                background-size: cover;
                background-position: center center;
                color: white;
                box-sizing: border-box;
            }
            
            .right-panel{
                width: 60%;
                height: 100%;
                display: flex;
                align-items: flex-start;
                justify-content: center;
                padding: 60px 5%;
                background: #f1f5f9;
                overflow-y: auto;
                box-sizing: border-box;
                background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
                background-size: 20px 20px;
            }
            .form-container{
                width: 100%;
                max-width: 520px;
            }
            .form-container h3{
                font-size: 32px;
                color: #0f172a;
                margin: 0 0 8px 0;
                text-align: center;
                font-weight: 700;
            }
            .form-container > p{
                color: #64748b;
                font-size: 15px;
                text-align: center;
                margin: 0 0 32px 0;
            }
            .form-subtitle {
                font-size: 16px;
                font-weight: 600;
                color: #1e293b;
                text-align: center;
                margin: 24px 0 4px 0;
            }
            .form-desc {
                font-size: 13px;
                color: #64748b;
                text-align: center;
                margin: 0 0 20px 0;
            }
            
            .form-group {
                margin-bottom: 16px;
                position: relative;
            }
            label{
                font-weight: 500;
                font-size: 14px;
                color: #344054;
                display: block;
                margin-bottom: 6px;
            }
            .input-wrapper {
                position: relative;
                display: flex;
                align-items: center;
            }
            .input-wrapper .input-icon {
                position: absolute;
                left: 14px;
                font-size: 16px;
                color: #94a3b8;
                pointer-events: none;
                filter: grayscale(100%);
            }
            input[type="text"],input[type="email"],input[type="password"]{
                width: 100%;
                padding: 12px 14px 12px 42px;
                border: 1.5px solid #cbd5e1;
                border-radius: 8px;
                font-size: 14px;
                box-sizing: border-box;
                outline: none;
                transition: border-color 0.2s ease;
                font-family: inherit;
                background: #ffffff;
                color: #1e293b;
            }
            .no-icon{
                padding-left: 14px;
            }
            textarea{
                resize: vertical;
                min-height: 90px;
            }
            input:focus, select:focus,textarea:focus{
                border-color: #2563eb;
                box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
            }
            input[type="submit"]{
                width: 100%;
                padding: 15px;
                background: #2563eb;
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                margin-top: 8px;
            }
            input[type="submit"]:hover{
                background:#1d4ed8;
            }
            input::placeholder, textarea::placeholder{
                color: #94a3b8;
            }
            .footer-text {
                text-align: center;
                margin-top: 24px;
                font-size: 14px;
                color: #64748b;
            }
            .footer-text a {
                color: #2563eb;
                text-decoration: none;
                font-weight: 600;
            }
            .footer-text a:hover {
                text-decoration: underline;
            }
            .btn-home {
               position: absolute;
               top: 20px;
               left: 20px;
               color: #ffffff;
               text-decoration: none;
               font-size: 14px;
               font-weight: 500;
               background: rgba(0,0,0,0.3);
               padding: 8px 14px;
               border-radius: 6px;
               z-index: 10;
               transition: background 0.2s;
            }
            .btn-home:hover{
                background: rgba(0,0,0,0.5);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="left-panel">
                <a href="landing_page.html" class="btn-home">← Back to Home</a>
                <img src="Tripistry_logo.jpg" alt="Logo" class="logo">
              <div class="hero-text" style="position: absolute; bottom: 40px; left: 32px; color: white;">
                  <h1 style="font-size: 36px; margin-bottom: 8px;">Welcome Back, Explorer!</h1>
                  <div class="destination-line" id="destination">✈ Explore Paris</div>
              </div>
            </div>
          <div class="right-panel">
            <div class="form-container">
                <h3>Welcome Back</h3>
                <p>Login to your account</p>
                
                <form id="login-form" action="api.php" method="POST">
                    <input type="hidden" name="type" value="Login">
                   <div class="form-group">
                       <div class="input-wrapper">
                           <span class="input-icon">✉</span>
                           <input type="email" id="email" name="email" placeholder="Email"><br>
                        </div>
                   </div>
                   <div class="form-group">
                       <div class="input-wrapper">
                          <span class="input-icon">🔒</span>
                          <input type="password" id="pword" name="password" placeholder="Password"><br>
                        </div>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                       <label style="display:flex; align-items:center; gap:6px; font-size:14px;">
                          <input type="checkbox" name="remember_me"> Remember me
                       </label>
                      <a href="forgot-password.html" style="font-size:14px; color:#2563eb; text-decoration:none;">Forgot password?</a>
                    </div>

                    <input type="submit" value="Login">
                </form>
                <p class="footer-text">Don't have an account? <a href="signup.php">Sign Up</a></p>
            </div>
            </div>
        </div>
        <script>
            const destinations=[
                "✈ Explore Paris",
                "🌏 Discover Tokyo",
                "🌍 Adventure in Cape Town",
                "🏝 Escape to Maldives",
                "🗽 Experience New York"
            ];
            let current = 0;
            const el = document.getElementById('destination');
            function cycleDestination(){
                el.classList.remove('visible');
                setTimeout(() => {
                    current = (current + 1) % destinations.length;
                    el.textContent = destinations[current];
                    el.classList.add('visible');
                }, 600);
            }
            el.classList.add('visible');
            setInterval(cycleDestination, 2500);
        </script>
    </body>
</html>