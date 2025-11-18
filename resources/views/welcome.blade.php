<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Symphony - Modern Pharmacy Management System</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            color: #111827;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #111827;
                color: #f9fafb;
            }
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        .hero-pattern {
            background-image: 
                radial-gradient(circle at 25px 25px, rgba(102, 126, 234, 0.05) 2%, transparent 0%),
                radial-gradient(circle at 75px 75px, rgba(118, 75, 162, 0.05) 2%, transparent 0%);
            background-size: 100px 100px;
        }
        
        a {
            text-decoration: none;
            color: inherit;
        }
        
        @media (min-width: 640px) {
            .cta-buttons {
                flex-direction: row !important;
            }
        }
    </style>
</head>
<body>
    
    <!-- Navigation -->
    <nav style="position: fixed; width: 100%; top: 0; z-index: 50; background-color: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid #e5e7eb;">
        <div style="max-width: 80rem; margin: 0 auto; padding: 0 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; height: 4rem;">
                <!-- Logo -->
                <div style="display: flex; align-items: center;">
                    <a href="/" style="display: flex; align-items: center; gap: 0.5rem;">
                        <svg style="width: 2rem; height: 2rem; color: #9333ea;" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 18c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6zm0-10c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4z"/>
                        </svg>
                        <span style="font-size: 1.25rem; font-weight: 700;" class="gradient-text">Symphony</span>
                    </a>
                </div>
                
                <!-- Navigation Links -->
                <div style="display: none; align-items: center; gap: 2rem;">
                    <a href="#features" style="color: #4b5563; transition: color 0.3s;">Features</a>
                    <a href="#about" style="color: #4b5563; transition: color 0.3s;">About</a>
                    <a href="#pricing" style="color: #4b5563; transition: color 0.3s;">Pricing</a>
                    <a href="#contact" style="color: #4b5563; transition: color 0.3s;">Contact</a>
                </div>
                
                <!-- Auth Links -->
                <div style="display: flex; align-items: center; gap: 1rem;">
                    @auth
                        <a href="{{ url('/dashboard') }}" 
                           style="padding: 0.5rem 1.5rem; background: linear-gradient(135deg, #9333ea 0%, #3b82f6 100%); color: white; border-radius: 0.5rem; font-weight: 600; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);"
                           onmouseover="this.style.background='linear-gradient(135deg, #7e22ce 0%, #2563eb 100%)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)'"
                           onmouseout="this.style.background='linear-gradient(135deg, #9333ea 0%, #3b82f6 100%)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'">
                            Go to Dashboard
                        </a>
                    @else
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" 
                               style="padding: 0.5rem 1.5rem; background: linear-gradient(135deg, #9333ea 0%, #3b82f6 100%); color: white; border-radius: 0.5rem; font-weight: 600; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);"
                               onmouseover="this.style.background='linear-gradient(135deg, #7e22ce 0%, #2563eb 100%)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)'"
                               onmouseout="this.style.background='linear-gradient(135deg, #9333ea 0%, #3b82f6 100%)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'">
                                Get Started Free
                            </a>
                        @endif
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" 
                               style="padding: 0.5rem 1.5rem; background-color: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); color: #4b5563; border-radius: 0.5rem; font-weight: 600; transition: all 0.2s; border: 1px solid rgba(0, 0, 0, 0.1);"
                               onmouseover="this.style.backgroundColor='rgba(255, 255, 255, 0.3)'; this.style.transform='translateY(-2px)'"
                               onmouseout="this.style.backgroundColor='rgba(255, 255, 255, 0.2)'; this.style.transform='translateY(0)'">
                                Sign In
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="hero-pattern" style="padding: 8rem 1rem 5rem; ">
        <div style="max-width: 80rem; margin: 0 auto;">
            <div style="display: grid; grid-template-columns: 1fr; gap: 3rem; align-items: center;">
                <!-- Left Content -->
                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    <div style="display: inline-block; padding: 0.5rem 1rem; background-color: #ede9fe; border-radius: 9999px; width: fit-content;">
                        <span style="color: #9333ea; font-weight: 600; font-size: 0.875rem;">💊 Modern Pharmacy Management</span>
                    </div>
                    
                    <h1 style="font-size: 3rem; font-weight: 700; line-height: 1.2;">
                        Streamline Your
                        <span class="gradient-text">Pharmacy Operations</span>
                    </h1>
                    
                    <p style="font-size: 1.25rem; color: #4b5563; line-height: 1.75;">
                        Symphony is a comprehensive pharmacy management system that handles inventory, sales, prescriptions, 
                        and customer management with ease. Built for modern pharmacies.
                    </p>
                    
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" style="padding: 1rem 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 0.5rem; transition: opacity 0.3s; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); text-align: center; font-weight: 600;">
                                Start Free Trial
                            </a>
                        @elseif (Route::has('login'))
                            <a href="{{ route('login') }}" style="padding: 1rem 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 0.5rem; transition: opacity 0.3s; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); text-align: center; font-weight: 600;">
                                Start Free Trial
                            </a>
                        @else
                            <a href="#features" style="padding: 1rem 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 0.5rem; transition: opacity 0.3s; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); text-align: center; font-weight: 600;">
                                Explore Features
                            </a>
                        @endif
                        <a href="#features" style="padding: 1rem 2rem; background-color: #f3f4f6; color: #111827; border-radius: 0.5rem; transition: background-color 0.3s; text-align: center; font-weight: 600;">
                            Learn More
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; padding-top: 2rem; border-top: 1px solid #e5e7eb;">
                        <div>
                            <div style="font-size: 1.875rem; font-weight: 700;" class="gradient-text">24/7</div>
                            <div style="font-size: 0.875rem; color: #4b5563;">Support</div>
                        </div>
                        <div>
                            <div style="font-size: 1.875rem; font-weight: 700;" class="gradient-text">Real-time</div>
                            <div style="font-size: 0.875rem; color: #4b5563;">Inventory</div>
                        </div>
                        <div>
                            <div style="font-size: 1.875rem; font-weight: 700;" class="gradient-text">M-Pesa</div>
                            <div style="font-size: 0.875rem; color: #4b5563;">Integrated</div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Illustration -->
                <div style="position: relative; display: none;">
                    <div style="position: relative;">
                        <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); opacity: 0.2; filter: blur(64px); border-radius: 9999px;"></div>
                        <svg class="float-animation" style="position: relative; width: 100%; height: auto;" viewBox="0 0 500 500" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="250" cy="250" r="200" fill="url(#gradient1)" opacity="0.1"/>
                            <circle cx="250" cy="250" r="150" fill="url(#gradient2)" opacity="0.2"/>
                            <circle cx="250" cy="250" r="100" fill="url(#gradient3)" opacity="0.3"/>
                            <path d="M250 150 L350 250 L250 350 L150 250 Z" fill="url(#gradient4)" opacity="0.5"/>
                            <defs>
                                <linearGradient id="gradient1" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
                                </linearGradient>
                                <linearGradient id="gradient2" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#f093fb;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#f5576c;stop-opacity:1" />
                                </linearGradient>
                                <linearGradient id="gradient3" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#4facfe;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#00f2fe;stop-opacity:1" />
                                </linearGradient>
                                <linearGradient id="gradient4" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section id="features" style="padding: 5rem 1rem; background-color: #f9fafb;">
        <div style="max-width: 80rem; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 4rem;">
                <h2 style="font-size: 2.25rem; font-weight: 700; margin-bottom: 1rem;">Complete Pharmacy Solution</h2>
                <p style="font-size: 1.25rem; color: #4b5563;">Everything you need to run a modern pharmacy</p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <!-- Feature 1 -->
                <div class="card-hover" style="background-color: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
                    <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                        <svg style="width: 1.5rem; height: 1.5rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem;">Inventory Management</h3>
                    <p style="color: #4b5563;">Track medicine stock levels, expiry dates, and batch numbers in real-time with automated alerts.</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="card-hover" style="background-color: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
                    <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                        <svg style="width: 1.5rem; height: 1.5rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem;">Prescription Management</h3>
                    <p style="color: #4b5563;">Digitally manage prescriptions, track controlled substances, and ensure compliance.</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="card-hover" style="background-color: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
                    <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                        <svg style="width: 1.5rem; height: 1.5rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem;">Point of Sale (POS)</h3>
                    <p style="color: #4b5563;">Fast checkout with barcode scanning, M-Pesa integration, and receipt printing.</p>
                </div>
                
                <!-- Feature 4 -->
                <div class="card-hover" style="background-color: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
                    <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                        <svg style="width: 1.5rem; height: 1.5rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem;">Customer Management</h3>
                    <p style="color: #4b5563;">Maintain customer profiles, purchase history, and loyalty programs effortlessly.</p>
                </div>
                
                <!-- Feature 5 -->
                <div class="card-hover" style="background-color: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
                    <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                        <svg style="width: 1.5rem; height: 1.5rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem;">Reports & Analytics</h3>
                    <p style="color: #4b5563;">Generate sales reports, profit analysis, and inventory insights for better decision making.</p>
                </div>
                
                <!-- Feature 6 -->
                <div class="card-hover" style="background-color: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);">
                    <div style="width: 3rem; height: 3rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
                        <svg style="width: 1.5rem; height: 1.5rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem;">Multi-User & Roles</h3>
                    <p style="color: #4b5563;">Manage multiple staff with role-based permissions and activity tracking.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section style="padding: 5rem 1rem;">
        <div style="max-width: 56rem; margin: 0 auto; text-align: center;">
            <div class="gradient-bg" style="border-radius: 1.5rem; padding: 3rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                <h2 style="font-size: 2.25rem; font-weight: 700; color: white; margin-bottom: 1.5rem;">Transform Your Pharmacy Today</h2>
                <p style="font-size: 1.25rem; color: rgba(255, 255, 255, 0.9); margin-bottom: 2rem;">Join pharmacies across Kenya using Symphony to modernize their operations and grow their business.</p>
                
                <!-- CTA Buttons -->
                <div class="cta-buttons" style="display: flex; flex-direction: column; gap: 1rem; justify-content: center; align-items: center; margin-bottom: 4rem;">
                    @auth
                        <a href="{{ url('/dashboard') }}" 
                           style="padding: 1rem 2rem; background: linear-gradient(135deg, #9333ea 0%, #3b82f6 100%); color: white; border-radius: 0.75rem; font-weight: 600; font-size: 1.125rem; width: 100%; max-width: 300px; text-align: center; transition: all 0.2s; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); transform: translateY(0);"
                           onmouseover="this.style.background='linear-gradient(135deg, #7e22ce 0%, #2563eb 100%)'; this.style.boxShadow='0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)'; this.style.transform='translateY(-4px)'"
                           onmouseout="this.style.background='linear-gradient(135deg, #9333ea 0%, #3b82f6 100%)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)'; this.style.transform='translateY(0)'">
                            Go to Dashboard
                        </a>
                    @else
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" 
                               style="padding: 1rem 2rem; background: linear-gradient(135deg, #9333ea 0%, #3b82f6 100%); color: white; border-radius: 0.75rem; font-weight: 600; font-size: 1.125rem; width: 100%; max-width: 300px; text-align: center; transition: all 0.2s; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); transform: translateY(0);"
                               onmouseover="this.style.background='linear-gradient(135deg, #7e22ce 0%, #2563eb 100%)'; this.style.boxShadow='0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)'; this.style.transform='translateY(-4px)'"
                               onmouseout="this.style.background='linear-gradient(135deg, #9333ea 0%, #3b82f6 100%)'; this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)'; this.style.transform='translateY(0)'">
                                Get Started Free
                            </a>
                        @endif
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" 
                               style="padding: 1rem 2rem; background-color: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); color: white; border-radius: 0.75rem; font-weight: 600; font-size: 1.125rem; width: 100%; max-width: 300px; text-align: center; transition: all 0.2s; border: 1px solid rgba(255, 255, 255, 0.3); transform: translateY(0);"
                               onmouseover="this.style.backgroundColor='rgba(255, 255, 255, 0.3)'; this.style.transform='translateY(-4px)'"
                               onmouseout="this.style.backgroundColor='rgba(255, 255, 255, 0.2)'; this.style.transform='translateY(0)'">
                                Sign In
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer style="background-color: #f9fafb; padding: 3rem 1rem;">
        <div style="max-width: 80rem; margin: 0 auto;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                        <svg style="width: 2rem; height: 2rem; color: #9333ea;" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 18c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6zm0-10c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4z"/>
                        </svg>
                        <span style="font-size: 1.25rem; font-weight: 700;" class="gradient-text">Symphony</span>
                    </div>
                    <p style="color: #4b5563;">Modern pharmacy management made simple.</p>
                </div>
                
                <div>
                    <h4 style="font-weight: 600; margin-bottom: 1rem;">Product</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.5rem;">
                        <li><a href="#features" style="color: #4b5563; transition: color 0.3s;">Features</a></li>
                        <li><a href="#pricing" style="color: #4b5563; transition: color 0.3s;">Pricing</a></li>
                        <li><a href="#" style="color: #4b5563; transition: color 0.3s;">Security</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 style="font-weight: 600; margin-bottom: 1rem;">Company</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.5rem;">
                        <li><a href="#about" style="color: #4b5563; transition: color 0.3s;">About</a></li>
                        <li><a href="#" style="color: #4b5563; transition: color 0.3s;">Blog</a></li>
                        <li><a href="#contact" style="color: #4b5563; transition: color 0.3s;">Contact</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 style="font-weight: 600; margin-bottom: 1rem;">Legal</h4>
                    <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.5rem;">
                        <li><a href="#" style="color: #4b5563; transition: color 0.3s;">Privacy</a></li>
                        <li><a href="#" style="color: #4b5563; transition: color 0.3s;">Terms</a></li>
                        <li><a href="#" style="color: #4b5563; transition: color 0.3s;">License</a></li>
                    </ul>
                </div>
            </div>
            
            <div style="border-top: 1px solid #e5e7eb; padding-top: 2rem; text-align: center; color: #4b5563;">
                <p>&copy; {{ date('Y') }} Symphony. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
</body>
</html>
