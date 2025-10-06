<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HTML element Counter</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <!-- Dummy header to make the page look nice -->
    <header class="site-header">
    <nav class="navbar">
        <div class="nav-brand">
            <a href="#" class="logo">
                <span class="logo-icon">🔍</span>
                HTML Element Counter
            </a>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="#" class="nav-link active">Home</a>
            </li>
            <li class="nav-item dropdown">
                <a href="#" class="nav-link">
                    Tools <span class="dropdown-arrow">▼</span>
                </a>
                <div class="dropdown-menu">
                    <a href="#" class="dropdown-link">Element Analyzer</a>
                    <a href="#" class="dropdown-link">Performance Metrics</a>
                    <a href="#" class="dropdown-link">SEO Checker</a>
                    <a href="#" class="dropdown-link">Code Validator</a>
                </div>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">Documentation</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">Pricing</a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">Blog</a>
            </li>
        </ul>
        
        <div class="nav-actions">
            <a href="#" class="btn-login">Log in</a>
            <a href="#" class="btn-signup">Sign up Free</a>
        </div>
        
        <div class="mobile-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>
</header>
    <div class="container">
        <h1>HTML element counter</h1>

        <form id="elementCounterForm">
            <div class="form-group">
                <label for="url">URL:</label>
                <input type="url" id="url" name="url" required
                    placeholder="https://example.com"
                    pattern="https?://.+"
                    title="Please enter a valid URL starting with http:// or https://">
            </div>

            <div class="form-group">
                <label for="element">HTML Element:</label>
                <input type="text" id="element" name="element" required
                    pattern="[a-zA-Z][a-zA-Z0-9]*"
                    title="Please enter a valid HTML elemnt name (letters and numbers only, starting with a letter)"
                    placeholder="img, div, p, etc.">
            </div>

            <button type="submit" id="submitBtn">Count Elements</button>
        </form>

        <div id="loading" class="hidden">
            <div class="spinner"></div>
            <p>Processing your request...</p>
        </div>

        <div id="results" class="hidden">
            <h2>Request Results</h2>
            <div id="requestResults"></div>

            <h2>General Statistics</h2>
            <div id="statistics"></div>
        </div>

        <div id="error" class="error hidden"></div>
    </div>
    <footer class="site-footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>HTML Element Counter</h3>
            <p>A powerful tool for web developers to analyze and count HTML elements across the web.</p>
            <div class="social-links">
                <a href="#" class="social-link">GitHub</a>
                <a href="#" class="social-link">Documentation</a>
                <a href="#" class="social-link">API</a>
            </div>
        </div>
        
        <div class="footer-section">
            <h4>Tools</h4>
            <ul class="footer-links">
                <li><a href="#">Element Analyzer</a></li>
                <li><a href="#">Page Statistics</a></li>
                <li><a href="#">Performance Metrics</a></li>
                <li><a href="#">SEO Checker</a></li>
                <li><a href="#">Code Validator</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h4>Resources</h4>
            <ul class="footer-links">
                <li><a href="#">Developer Guide</a></li>
                <li><a href="#">Tutorials</a></li>
                <li><a href="#">Blog</a></li>
                <li><a href="#">Case Studies</a></li>
                <li><a href="#">Web Standards</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h4>Company</h4>
            <ul class="footer-links">
                <li><a href="#">About Us</a></li>
                <li><a href="#">Careers</a></li>
                <li><a href="#">Contact</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h4>Support</h4>
            <ul class="footer-links">
                <li><a href="#">Help Center</a></li>
                <li><a href="#">Community Forum</a></li>
                <li><a href="#">Status Page</a></li>
                <li><a href="#">Report Issue</a></li>
                <li><a href="#">Feature Request</a></li>
            </ul>
        </div>
    </div>
    <!-- Added a dummy footer to make the tool look nice -->
    <div class="footer-bottom">
        <div class="footer-info">
            <p>&copy; 2024 HTML Element Counter. All rights reserved.</p>
            <div class="footer-meta">
                <span>Version 2.1.3</span>
                <span class="separator">|</span>
                <span>Updated: Dec 2024</span>
                <span class="separator">|</span>
                <span class="status-indicator">
                    <span class="status-dot"></span>
                    All systems operational
                </span>
            </div>
        </div>
        <div class="footer-badges">
            <span class="badge">SSL Secured</span>
            <span class="badge">GDPR Compliant</span>
            <span class="badge">Open Source</span>
        </div>
    </div>
</footer>

    <script src="script.js"></script>
</body>

</html>