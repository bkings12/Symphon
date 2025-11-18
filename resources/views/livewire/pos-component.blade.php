<style>
    /* Modern Color Palette - Vibrant & Professional */
    :root {
        --primary: #6366f1;
        --primary-dark: #4f46e5;
        --primary-light: #e0e7ff;
        --success: #10b981;
        --success-dark: #059669;
        --success-light: #d1fae5;
        --danger: #ef4444;
        --danger-dark: #dc2626;
        --warning: #f59e0b;
        --info: #06b6d4;
        --purple: #a855f7;
        --dark: #1e293b;
        --gray-50: #f8fafc;
        --gray-100: #f1f5f9;
        --gray-200: #e2e8f0;
        --gray-300: #cbd5e1;
        --gray-400: #94a3b8;
        --gray-500: #64748b;
        --gray-600: #475569;
        --gray-700: #334155;
        --gray-800: #1e293b;
        --gray-900: #0f172a;
    }

    /* Base Styles - Scoped to POS container */
    .pos-container .pos-container { 
        min-height: 100vh; 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        padding: 1.5rem;
        width: 100%;
        box-sizing: border-box;
    }
    
    /* Reset styles scoped to POS container only */
    .pos-container * {
        box-sizing: border-box;
    }
    
    /* Glass Morphism Effect - Scoped to POS */
    .pos-container .glass {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    /* Header Styles - Scoped to POS */
    .pos-container .pos-header { 
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        border-radius: 1.25rem;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        padding: 2rem;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    
    .pos-container .pos-header::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.3) 0%, transparent 70%);
    }
    
    .pos-container .pos-header-content { 
        display: flex; 
        justify-content: space-between; 
        align-items: center;
        position: relative;
        z-index: 1;
        flex-wrap: nowrap;
        gap: 1.5rem;
        width: 100%;
    }
    
    .pos-container .pos-header-content > div:first-child {
        flex: 0 1 auto;
        min-width: 0;
    }
    
    .pos-container .pos-header-content > div:last-child {
        flex: 0 0 auto;
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-shrink: 0;
    }
    
    .pos-container .pos-header-title { 
        font-size: 2rem; 
        font-weight: 800; 
        color: white;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        white-space: nowrap;
    }
    
    .pos-container .pos-header-title::before {
        content: '🛒';
        font-size: 2.5rem;
    }
    
    .pos-container .pos-header-subtitle { 
        color: rgba(255,255,255,0.8);
        margin-top: 0.5rem;
        font-size: 1rem;
        font-weight: 500;
    }
    
    .pos-container .pos-header-actions { 
        display: flex; 
        gap: 1rem; 
        align-items: center;
        flex-wrap: nowrap;
        flex-shrink: 0;
    }
    
    /* Customer Badge */
    .pos-container .pos-customer-badge { 
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
        transition: all 0.3s ease;
    }
    
    .pos-container .pos-customer-badge:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(99, 102, 241, 0.4);
    }
    
    .pos-container .pos-customer-badge p:first-child {
        font-size: 0.75rem;
        opacity: 0.9;
        margin-bottom: 0.25rem;
    }
    
    .pos-container .pos-customer-badge p:last-child {
        font-weight: 700;
        font-size: 1.125rem;
    }
    
    /* Checkout Button */
    .pos-container .pos-button-checkout { 
        padding: 1.25rem 2.5rem;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border-radius: 1rem;
        font-weight: 700;
        font-size: 1.25rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
        overflow: hidden;
    }
    
    .pos-container .pos-button-checkout::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }
    
    .pos-container .pos-button-checkout:hover:not(:disabled)::before {
        left: 100%;
    }
    
    .pos-container .pos-button-checkout:hover:not(:disabled) { 
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 15px 40px rgba(16, 185, 129, 0.5);
    }
    
    .pos-container .pos-button-checkout:active:not(:disabled) {
        transform: translateY(-1px) scale(1);
    }
    
    .pos-container .pos-button-checkout:disabled { 
        background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);
        cursor: not-allowed;
        box-shadow: none;
        opacity: 0.6;
    }
    
    /* Grid Layout */
    .pos-container .pos-grid { 
        display: grid; 
        grid-template-columns: 1fr; 
        gap: 1.5rem; 
    }
    
    @media (min-width: 1024px) { 
        .pos-container .pos-grid { 
            grid-template-columns: 1.5fr 1fr; 
        } 
    }
    
    /* Cards */
    .pos-container .pos-card { 
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 1.25rem;
        box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        padding: 1.75rem;
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    
    .pos-container .pos-card:hover {
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    
    /* Search Section */
    .pos-container .pos-search-wrapper { 
        position: relative; 
        margin-bottom: 1rem;
    }
    
    .pos-container .pos-search-input { 
        width: 100%;
        padding: 1.25rem 4rem 1.25rem 3.5rem;
        font-size: 1.125rem;
        border: 2px solid var(--gray-200);
        border-radius: 1rem;
        transition: all 0.3s ease;
        background: white;
        color: var(--gray-900);
        font-weight: 500;
    }
    
    .pos-container .pos-search-input:focus { 
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        transform: translateY(-2px);
    }
    
    .pos-container .pos-search-icon {
        position: absolute;
        left: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
        pointer-events: none;
    }
    
    /* Search Results */
    .pos-container .pos-search-results { 
        margin-top: 1rem;
        border: 2px solid var(--gray-200);
        border-radius: 1rem;
        background: white;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        max-height: 500px;
        overflow-y: auto;
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .pos-container .pos-search-result-item { 
        width: 100%;
        padding: 1.25rem 1.5rem;
        text-align: left;
        border: none;
        background: white;
        border-bottom: 1px solid var(--gray-100);
        cursor: default;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }
    
    .pos-container .pos-search-result-item:hover { 
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        transform: translateX(5px);
    }
    
    .pos-container .pos-search-result-item:last-child { 
        border-bottom: none; 
    }
    
    .pos-container .pos-result-name { 
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
        font-size: 1.125rem;
    }
    
    .pos-container .pos-result-details { 
        font-size: 0.875rem;
        color: var(--gray-600);
        margin: 0.25rem 0 0 0;
    }
    
    .pos-container .pos-result-meta { 
        font-size: 0.75rem;
        color: var(--gray-500);
        margin: 0.25rem 0 0 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    
    .pos-container .pos-result-price { 
        font-weight: 800;
        color: var(--success);
        font-size: 1.5rem;
        margin: 0;
    }
    
    .pos-container .pos-add-to-cart-btn {
        padding: 0.875rem 1.75rem;
        background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
        color: white;
        border: none;
        border-radius: 0.75rem;
        font-weight: 700;
        cursor: pointer;
        font-size: 0.9375rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }
    
    .pos-container .pos-add-to-cart-btn:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.5);
    }
    
    .pos-container .pos-add-to-cart-btn:active {
        transform: translateY(-1px) scale(1.02);
    }
    
    /* Section Headers */
    .pos-container .pos-section-title { 
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--gray-800);
        margin: 0 0 1rem 0;
        position: relative;
        padding-left: 1rem;
    }
    
    .pos-container .pos-section-title::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 100%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--purple) 100%);
        border-radius: 2px;
    }
    
    /* Forms and Inputs */
    .pos-input, .pos-select { 
        width: 100%;
        padding: 0.875rem 1.25rem;
        border: 2px solid var(--gray-200);
        border-radius: 0.75rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
        color: var(--gray-900);
        font-weight: 500;
    }
    
    .pos-container .pos-input:focus, .pos-select:focus { 
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        transform: translateY(-2px);
    }
    
    .pos-container .pos-label { 
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--gray-700);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Customer Section */
    .pos-container .pos-customer-selected { 
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        padding: 1.5rem;
        border-radius: 1rem;
        border: 2px solid var(--primary-light);
        position: relative;
        overflow: hidden;
    }
    
    .pos-container .pos-customer-selected::before {
        content: '👤';
        position: absolute;
        right: 1.5rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 3rem;
        opacity: 0.15;
    }
    
    /* Cart Sidebar */
    .pos-container .pos-cart-sidebar { 
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 1.25rem;
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        padding: 1.75rem;
        position: sticky;
        top: 1.5rem;
        border: 2px solid rgba(99, 102, 241, 0.1);
        max-height: calc(100vh - 3rem);
        overflow-y: auto;
    }
    
    .pos-container .pos-cart-header { 
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--gray-200);
    }
    
    .pos-container .pos-cart-title { 
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--gray-900);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .pos-container .pos-cart-title::before {
        content: '🛒';
        font-size: 1.5rem;
    }
    
    /* Cart Items */
    .pos-container .pos-cart-empty { 
        text-align: center;
        padding: 3rem 1rem;
    }
    
    .pos-container .pos-cart-empty-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }
    
    .pos-container .pos-cart-items { 
        display: flex;
        flex-direction: column;
        gap: 1rem;
        max-height: 400px;
        overflow-y: auto;
        margin-bottom: 1.5rem;
        padding-right: 0.5rem;
    }
    
    .pos-container .pos-cart-items::-webkit-scrollbar {
        width: 6px;
    }
    
    .pos-container .pos-cart-items::-webkit-scrollbar-track {
        background: var(--gray-100);
        border-radius: 3px;
    }
    
    .pos-container .pos-cart-items::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 3px;
    }
    
    .pos-container .pos-cart-item { 
        border: 2px solid var(--gray-200);
        border-radius: 1rem;
        padding: 1.25rem;
        transition: all 0.3s ease;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .pos-container .pos-cart-item:hover {
        border-color: var(--primary);
        background: var(--gray-50);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .pos-container .pos-cart-item-name { 
        font-weight: 700;
        color: var(--gray-900);
        font-size: 1.0625rem;
        margin: 0;
    }
    
    .pos-container .pos-cart-item-sku { 
        font-size: 0.75rem;
        color: var(--gray-500);
        margin: 0.25rem 0 0 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    
    /* Quantity Controls */
    .pos-container .pos-cart-qty-btn { 
        width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--gray-200);
        border: none;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 700;
    }
    
    .pos-container .pos-cart-qty-btn:hover { 
        background: var(--primary);
        color: white;
        transform: scale(1.1);
    }
    
    .pos-container .pos-cart-qty-input { 
        width: 4rem;
        text-align: center;
        border: 2px solid var(--gray-300);
        border-radius: 0.5rem;
        padding: 0.5rem;
        font-weight: 700;
        color: var(--gray-900);
        font-size: 1rem;
    }
    
    /* Cart Totals */
    .pos-container .pos-cart-totals { 
        border-top: 2px solid var(--gray-200);
        padding-top: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .pos-container .pos-cart-total-row { 
        display: flex;
        justify-content: space-between;
        font-size: 1rem;
        font-weight: 600;
        color: var(--gray-700);
    }
    
    .pos-container .pos-cart-total-row-final { 
        display: flex;
        justify-content: space-between;
        font-size: 1.75rem;
        font-weight: 800;
        padding: 1.25rem;
        margin-top: 0.5rem;
        background: linear-gradient(135deg, var(--success-light) 0%, #a7f3d0 100%);
        border-radius: 1rem;
        color: var(--success-dark);
    }
    
    /* Modal Styles */
    .pos-container .pos-modal-overlay { 
        position: fixed;
        inset: 0;
        background-color: rgba(0, 0, 0, 0.75);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 50;
        backdrop-filter: blur(8px);
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .pos-container     .pos-modal-content { 
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 25px 80px rgba(0,0,0,0.3);
        max-width: 34rem;
        width: 100%;
        margin: 0 1rem;
        animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        max-height: 90vh;
        display: flex;
        flex-direction: column;
    }
    
    .pos-modal-content#receipt-modal {
        max-height: 85vh;
    }
    
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-30px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }
    
    .pos-container     .pos-modal-inner { 
        padding: 2.5rem;
        display: flex;
        flex-direction: column;
        max-height: 100%;
        overflow: hidden;
    }
    
    .pos-modal-receipt-content {
        flex: 1;
        overflow-y: auto;
        padding-right: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .pos-modal-receipt-content::-webkit-scrollbar {
        width: 6px;
    }
    
    .pos-modal-receipt-content::-webkit-scrollbar-track {
        background: var(--gray-100);
        border-radius: 3px;
    }
    
    .pos-modal-receipt-content::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 3px;
    }
    
    .pos-modal-actions {
        flex-shrink: 0;
        margin-top: auto;
        padding-top: 1.5rem;
        border-top: 2px solid var(--gray-200);
    }
    
    .pos-container .pos-modal-title { 
        font-size: 2rem;
        font-weight: 800;
        color: var(--gray-900);
        margin-bottom: 2rem;
        text-align: center;
        position: relative;
        padding-bottom: 1rem;
    }
    
    .pos-container .pos-modal-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--purple) 100%);
        border-radius: 2px;
    }
    
    /* Modal Info Boxes */
    .pos-container .pos-modal-info-box { 
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        padding: 1.5rem;
        border-radius: 1rem;
        border: 2px solid var(--gray-300);
    }
    
    .pos-container .pos-modal-change-box { 
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        padding: 1.5rem;
        border-radius: 1rem;
        border: 2px solid var(--success);
        animation: pulseGreen 2s infinite;
    }
    
    @keyframes pulseGreen {
        0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
        50% { box-shadow: 0 0 0 15px rgba(16, 185, 129, 0); }
    }
    
    .pos-container .pos-modal-change-amount { 
        color: var(--success-dark);
        font-weight: 800;
        font-size: 1.75rem;
    }
    
    /* Modal Buttons */
    .pos-container .pos-modal-button { 
        flex: 1;
        padding: 1rem 1.75rem;
        border-radius: 1rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 1rem;
    }
    
    .pos-container .pos-modal-button-cancel { 
        border: 2px solid var(--gray-300);
        color: var(--gray-700);
        background: white;
    }
    
    .pos-container .pos-modal-button-cancel:hover { 
        background: var(--gray-100);
        border-color: var(--gray-400);
        transform: translateY(-2px);
    }
    
    .pos-container .pos-modal-button-submit { 
        background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
        color: white;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
    }
    
    .pos-container .pos-modal-button-submit:hover:not(:disabled) { 
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 12px 30px rgba(16, 185, 129, 0.5);
    }
    
    .pos-container .pos-modal-button-submit:disabled { 
        background: var(--gray-300);
        box-shadow: none;
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    /* Utility Classes */
    .pos-container .pos-button-remove { 
        color: var(--danger);
        background: rgba(239, 68, 68, 0.1);
        border: none;
        font-size: 0.875rem;
        cursor: pointer;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        font-weight: 600;
    }
    
    .pos-container .pos-button-remove:hover { 
        background: var(--danger);
        color: white;
        transform: scale(1.05);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .pos-container .pos-header-title { font-size: 1.5rem; }
        .pos-container .pos-button-checkout { padding: 1rem 1.5rem; font-size: 1rem; }
        .pos-container .pos-cart-sidebar { position: relative; top: 0; }
        .pos-container .pos-header-content {
            flex-wrap: wrap;
        }
        .pos-container .pos-header-actions {
            flex-wrap: wrap;
            width: 100%;
            justify-content: flex-end;
        }
    }
</style>

<div class="pos-container">
    <div style="max-width: 96rem; margin: 0 auto;">
        <!-- Header -->
        <div class="pos-header">
            <div class="pos-header-content">
                <div>
                    <h1 class="pos-header-title">Point of Sale</h1>
                    <p class="pos-header-subtitle">{{ auth()->user()->pharmacy?->name ?? 'Symphony Pharmacy' }}</p>
                </div>
                <div class="pos-header-actions">
                    @if($selectedCustomer)
                        <div class="pos-customer-badge">
                            <p>Customer</p>
                            <p>{{ $selectedCustomer['name'] }}</p>
                        </div>
                    @endif
                    <button 
                        wire:click="openPaymentModal"
                        @if(empty($cart)) disabled @endif
                        class="pos-button-checkout">
                        💳 Checkout • {{ format_currency($cartTotal) }}
                    </button>
                </div>
            </div>
        </div>

        <div class="pos-grid">
            <!-- Main Content -->
            <div>
                <!-- Search Bar -->
                <div class="pos-card">
                    <div class="pos-search-wrapper">
                        <svg class="pos-search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 1.5rem; height: 1.5rem;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            placeholder="🔍 Search by name, barcode, or SKU..."
                            class="pos-search-input"
                            id="search-input"
                            autofocus>
                    </div>

                    <!-- Search Results -->
                    @if($showSearchResults && count($searchResults) > 0)
                        <div class="pos-search-results">
                            <div style="padding: 1rem 1.5rem; border-bottom: 2px solid var(--gray-200); background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                                <p style="margin: 0; font-weight: 700; color: var(--gray-700); font-size: 0.9375rem;">
                                    @if(strlen($search) >= 2)
                                        🎯 Found {{ count($searchResults) }} {{ count($searchResults) == 1 ? 'medicine' : 'medicines' }}
                                    @else
                                        💊 Available Medicines
                                    @endif
                                </p>
                            </div>
                            @foreach($searchResults as $result)
                                <div class="pos-search-result-item">
                                    <div style="flex: 1;">
                                        <p class="pos-result-name">{{ $result['name'] }}</p>
                                        <p class="pos-result-details">{{ $result['generic_name'] }}</p>
                                        <p class="pos-result-meta">SKU: {{ $result['sku'] }} • Stock: {{ $result['stock_quantity'] }} {{ $result['unit'] }}</p>
                                    </div>
                                    <div style="text-align: right;">
                                        <p class="pos-result-price">{{ format_currency($result['selling_price']) }}</p>
                                    </div>
                                    <button 
                                        type="button"
                                        wire:click="addToCart({{ (int)$result['id'] }})"
                                        class="pos-add-to-cart-btn">
                                        + Add
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Customer Selection -->
                <div class="pos-card" style="margin-top: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h2 class="pos-section-title" style="margin: 0;">👤 Customer</h2>
                        @if($selectedCustomer)
                            <button wire:click="removeCustomer" class="pos-button-remove">
                                ✕ Remove
                            </button>
                        @endif
                    </div>
                    
                    @if(!$selectedCustomer)
                        <div style="position: relative;">
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="customerSearch"
                                placeholder="Search customer by name, phone, or email..."
                                class="pos-input">
                            
                            @if(count($customerResults) > 0)
                                <div style="position: absolute; z-index: 10; width: 100%; margin-top: 0.75rem; background: white; border: 2px solid var(--gray-200); border-radius: 1rem; box-shadow: 0 15px 40px rgba(0,0,0,0.2); max-height: 20rem; overflow-y: auto;">
                                    @foreach($customerResults as $customer)
                                        <button 
                                            wire:click="selectCustomer({{ $customer['id'] }})"
                                            style="width: 100%; padding: 1rem 1.25rem; text-align: left; border: none; background: white; cursor: pointer; transition: all 0.2s ease; border-bottom: 1px solid var(--gray-100);"
                                            onmouseover="this.style.background='var(--gray-50)'; this.style.transform='translateX(5px)';"
                                            onmouseout="this.style.background='white'; this.style.transform='translateX(0)';">
                                            <p style="font-weight: 700; margin: 0; color: var(--gray-900); font-size: 1.0625rem;">{{ $customer['name'] }}</p>
                                            <p style="font-size: 0.875rem; color: var(--gray-600); margin: 0.25rem 0 0 0;">📞 {{ $customer['phone'] }} • ✉️ {{ $customer['email'] }}</p>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="pos-customer-selected">
                            <p style="font-weight: 700; color: var(--gray-900); margin: 0; font-size: 1.25rem;">{{ $selectedCustomer['name'] }}</p>
                            <p style="font-size: 1rem; color: var(--gray-600); margin: 0.5rem 0 0 0;">📞 {{ $selectedCustomer['phone'] }}</p>
                        </div>
                    @endif
                </div>

                <!-- Discount Section -->
                <div class="pos-card" style="margin-top: 1.5rem;">
                    <h2 class="pos-section-title">💰 Discount & Promotions</h2>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label class="pos-label">Discount Type</label>
                            <select wire:model.live="discountType" class="pos-select">
                                <option value="fixed">💵 Fixed Amount ($)</option>
                                <option value="percentage">📊 Percentage (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="pos-label">Discount Value</label>
                            <input 
                                type="number" 
                                wire:model.live.debounce.300ms="discountValue"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="pos-input">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart Sidebar -->
            <div>
                <div class="pos-cart-sidebar">
                    <div class="pos-cart-header">
                        <h2 class="pos-cart-title">Shopping Cart</h2>
                        @if(count($cart) > 0)
                            <button wire:click="clearCart" class="pos-button-remove">
                                🗑️ Clear
                            </button>
                        @endif
                    </div>

                    @if(empty($cart))
                        <div class="pos-cart-empty">
                            <div class="pos-cart-empty-icon">🛒</div>
                            <p style="font-size: 1.125rem; color: var(--gray-600); margin-bottom: 0.5rem; font-weight: 600;">Your cart is empty</p>
                            <p style="font-size: 0.9375rem; color: var(--gray-500);">Start adding items to checkout</p>
                        </div>
                    @else
                        <div class="pos-cart-items">
                            @foreach($cart as $index => $item)
                                <div class="pos-cart-item">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                        <div style="flex: 1;">
                                            <p class="pos-cart-item-name">{{ $item['name'] }}</p>
                                            <p class="pos-cart-item-sku">{{ $item['sku'] }}</p>
                                        </div>
                                        <button 
                                            wire:click="removeFromCart({{ $index }})"
                                            style="color: var(--danger); background: rgba(239, 68, 68, 0.1); border: none; padding: 0.5rem; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s ease;"
                                            onmouseover="this.style.background='var(--danger)'; this.style.color='white';"
                                            onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'; this.style.color='var(--danger)';">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 1.25rem; height: 1.25rem;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div style="display: flex; align-items: center; justify-content: space-between;">
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <button 
                                                wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                                class="pos-cart-qty-btn">
                                                −
                                            </button>
                                            <input 
                                                type="number" 
                                                wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                                value="{{ $item['quantity'] }}"
                                                min="1"
                                                max="{{ $item['stock_available'] }}"
                                                class="pos-cart-qty-input">
                                            <button 
                                                wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                                class="pos-cart-qty-btn">
                                                +
                                            </button>
                                        </div>
                                        <p style="font-weight: 800; color: var(--gray-900); font-size: 1.25rem; margin: 0;">
                                            {{ format_currency($item['selling_price'] * $item['quantity']) }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Cart Totals -->
                        <div class="pos-cart-totals">
                            <div class="pos-cart-total-row">
                                <span>Subtotal</span>
                                <span>{{ format_currency($cartSubtotal) }}</span>
                            </div>
                            @if($discountAmount > 0)
                                <div class="pos-cart-total-row" style="color: var(--success);">
                                    <span>💸 Discount</span>
                                    <span>-{{ format_currency($discountAmount) }}</span>
                                </div>
                            @endif
                            <div class="pos-cart-total-row">
                                <span>Tax ({{ $taxRate * 100 }}%)</span>
                                <span>{{ format_currency($taxAmount) }}</span>
                            </div>
                            <div class="pos-cart-total-row-final">
                                <span>Total</span>
                                <span>{{ format_currency($cartTotal) }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    @if($showPaymentModal)
        <div class="pos-modal-overlay" wire:click.self="closePaymentModal">
            <div class="pos-modal-content">
                <div class="pos-modal-inner">
                    <h2 class="pos-modal-title">💳 Payment</h2>
                    
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div>
                            <label class="pos-label">Payment Method</label>
                            <select wire:model.live="paymentMethod" wire:change="$refresh" class="pos-select" style="font-size: 1.0625rem;">
                                <option value="cash">💵 Cash</option>
                                <option value="mpesa">📱 M-Pesa</option>
                                <option value="bank_paybill">🏦 Bank Paybill</option>
                            </select>
                            <input type="hidden" wire:model="paymentMethod" />
                        </div>

                        <div class="pos-modal-info-box">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: var(--gray-600); font-weight: 600; font-size: 1rem;">Total Amount Due</span>
                                <span style="font-size: 2rem; font-weight: 800; color: var(--gray-900);">{{ format_currency($cartTotal) }}</span>
                            </div>
                        </div>

                        @if($paymentMethod === 'mpesa' || $paymentMethod === 'bank_paybill')
                            <div>
                                <label class="pos-label">Phone Number <span style="color: red;">*</span></label>
                                <input 
                                    type="tel" 
                                    wire:model.live="stkPhoneNumber"
                                    placeholder="07XXXXXXXX, 0111XXXXXX, or 254XXXXXXXXX"
                                    class="pos-input"
                                    style="font-size: 1.25rem; font-weight: 600; padding: 1rem;"
                                    required
                                    autofocus>
                                <p style="color: var(--gray-500); font-size: 0.875rem; margin-top: 0.5rem;">
                                    @if($selectedCustomer && $selectedCustomer['phone'])
                                        Customer phone: {{ $selectedCustomer['phone'] }} (you can change it)
                                    @else
                                        Enter the phone number for STK Push payment
                                    @endif
                                </p>
                            </div>

                            @if(isset($stkStatus) && $stkStatus === 'pending')
                                <div 
                                    wire:poll.3s="checkSTKPaymentStatus"
                                    style="background: var(--info-light); border: 2px solid var(--info); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                        <svg class="animate-spin" style="width: 1.5rem; height: 1.5rem; color: var(--info);" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span style="color: var(--info-dark); font-weight: 700; font-size: 1rem;">Waiting for payment...</span>
                                    </div>
                                    <p style="color: var(--gray-600); font-size: 0.875rem;">
                                        Please check your phone and enter your M-Pesa PIN to complete the payment.
                                    </p>
                                </div>
                            @endif

                            @if(isset($stkStatus) && $stkStatus === 'success')
                                <div style="background: var(--success-light); border: 2px solid var(--success); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                        <svg style="width: 1.5rem; height: 1.5rem; color: var(--success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span style="color: var(--success-dark); font-weight: 700; font-size: 1rem;">Payment Successful!</span>
                                    </div>
                                    <p style="color: var(--gray-600); font-size: 0.875rem;">
                                        Payment has been completed successfully.
                                    </p>
                                </div>
                            @endif

                            @if(isset($stkStatus) && $stkStatus === 'failed')
                                <div style="background: var(--danger-light); border: 2px solid var(--danger); border-radius: 0.75rem; padding: 1.25rem; text-align: center;">
                                    <span style="color: var(--danger-dark); font-weight: 700; font-size: 1rem;">Payment Failed</span>
                                    <p style="color: var(--gray-600); font-size: 0.875rem; margin-top: 0.5rem;">
                                        Please try again or use a different payment method.
                                    </p>
                                </div>
                            @endif
                        @else
                            <div>
                                <label class="pos-label">Amount Paid</label>
                                <input 
                                    type="number" 
                                    wire:model.live="amountPaid"
                                    step="0.01"
                                    min="0"
                                    class="pos-input"
                                    style="font-size: 1.5rem; font-weight: 700; padding: 1.25rem;"
                                    autofocus>
                            </div>

                            @if($change > 0)
                                <div class="pos-modal-change-box">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="color: var(--success-dark); font-weight: 700; font-size: 1.25rem;">💰 Change Due</span>
                                        <span class="pos-modal-change-amount">{{ format_currency($change) }}</span>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                            <button 
                                wire:click="closePaymentModal"
                                class="pos-modal-button pos-modal-button-cancel"
                                @if(isset($stkStatus) && $stkStatus === 'pending') disabled @endif>
                                Cancel
                            </button>
                            <button 
                                wire:click="processSale"
                                wire:loading.attr="disabled"
                                wire:target="processSale"
                                class="pos-modal-button pos-modal-button-submit"
                                @if(isset($stkStatus) && $stkStatus === 'pending') disabled @endif>
                                @if($paymentMethod === 'mpesa' || $paymentMethod === 'bank_paybill')
                                    <span wire:loading.remove wire:target="processSale">
                                        @if(isset($stkStatus) && $stkStatus === 'pending')
                                            ⏳ Processing...
                                        @else
                                            📱 Pay with STK Push
                                        @endif
                                    </span>
                                    <span wire:loading wire:target="processSale">Processing...</span>
                                @else
                                    <span wire:loading.remove wire:target="processSale">✓ Complete Sale</span>
                                    <span wire:loading wire:target="processSale">Processing...</span>
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif


    <!-- Receipt Modal -->
    @isset($showReceiptModal)
        @if($showReceiptModal && isset($lastSale) && $lastSale)
        <div class="pos-modal-overlay" style="z-index: 60;">
            <div class="pos-modal-content" style="max-width: 500px;" id="receipt-modal">
                <div class="pos-modal-inner">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--gray-900); margin: 0;">
                            🧾 Receipt
                        </h2>
                        <button 
                            wire:click="closeReceipt"
                            style="color: var(--gray-500); background: none; border: none; padding: 0.5rem; cursor: pointer; border-radius: 0.5rem; transition: all 0.2s ease;"
                            onmouseover="this.style.background='var(--gray-100)'; this.style.color='var(--gray-900)';"
                            onmouseout="this.style.background='none'; this.style.color='var(--gray-500)';">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 1.5rem; height: 1.5rem;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Receipt Content - Scrollable -->
                    <div class="pos-modal-receipt-content">
                        <x-receipt 
                            :sale="$lastSale" 
                            :saleItems="$lastSaleItems"
                            :customer="$lastCustomer" 
                            :payment="$lastPayment" 
                        />
                    </div>

                    <!-- Action Buttons - Fixed at bottom -->
                    <div class="pos-modal-actions" style="display: flex; gap: 1rem;">
                        <button 
                            wire:click="printReceipt"
                            style="flex: 1; padding: 1rem 1.5rem; background: linear-gradient(135deg, var(--primary) 0%, var(--purple) 100%); color: white; border: none; border-radius: 0.75rem; font-weight: 700; cursor: pointer; font-size: 1rem; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(99, 102, 241, 0.4)';"
                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(99, 102, 241, 0.3)';">
                            🖨️ Print Receipt
                        </button>
                        <button 
                            wire:click="closeReceipt"
                            style="flex: 1; padding: 1rem 1.5rem; background: white; color: var(--gray-700); border: 2px solid var(--gray-300); border-radius: 0.75rem; font-weight: 700; cursor: pointer; font-size: 1rem; transition: all 0.3s ease;"
                            onmouseover="this.style.background='var(--gray-100)'; this.style.borderColor='var(--gray-400)';"
                            onmouseout="this.style.background='white'; this.style.borderColor='var(--gray-300)';">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endisset

    <!-- Notification Toast -->
    <div x-data="{ show: false, message: '', type: 'success' }" 
         @notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 3000)"
         x-show="show"
         x-transition
         style="position: fixed; bottom: 2rem; right: 2rem; z-index: 50;">
        <div style="background: white; border-radius: 1rem; box-shadow: 0 10px 40px rgba(0,0,0,0.2); padding: 1.25rem 1.75rem; border-left: 5px solid; min-width: 300px;"
             :style="'border-left-color: ' + (type === 'success' ? 'var(--success)' : 'var(--danger)')">
            <p style="font-weight: 700; font-size: 1.0625rem; margin: 0;" 
               :style="'color: ' + (type === 'success' ? 'var(--success-dark)' : 'var(--danger-dark)')"
               x-text="message"></p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('focus-search', () => {
            document.getElementById('search-input')?.focus();
        });

        Livewire.on('sale-completed', (data) => {
            console.log('Sale completed:', data.invoiceNumber);
        });

        Livewire.on('print-receipt', () => {
            // Find receipt container
            const receiptContainer = document.querySelector('.receipt-container');
            if (!receiptContainer) {
                console.error('Receipt container not found');
                return;
            }
            
            // Clone receipt and append to body for printing
            const receiptClone = receiptContainer.cloneNode(true);
            receiptClone.id = 'receipt-print-clone';
            receiptClone.style.position = 'relative';
            receiptClone.style.left = 'auto';
            receiptClone.style.top = 'auto';
            receiptClone.style.transform = 'none';
            receiptClone.style.width = '80mm';
            receiptClone.style.maxWidth = '80mm';
            receiptClone.style.margin = '0 auto';
            receiptClone.style.padding = '1rem';
            receiptClone.style.background = 'white';
            receiptClone.style.zIndex = '99999';
            receiptClone.style.display = 'block';
            receiptClone.style.visibility = 'visible';
            receiptClone.style.pageBreakAfter = 'avoid';
            receiptClone.style.pageBreakInside = 'avoid';
            
            // Store original body styles
            const originalBodyStyle = {
                height: document.body.style.height,
                overflow: document.body.style.overflow,
                margin: document.body.style.margin,
                padding: document.body.style.padding
            };
            
            // Set body styles for printing
            document.body.style.height = 'auto';
            document.body.style.overflow = 'visible';
            document.body.style.margin = '0';
            document.body.style.padding = '0';
            
            // Hide everything else
            const bodyChildren = Array.from(document.body.children);
            const hiddenElements = [];
            bodyChildren.forEach(child => {
                if (child.id !== 'receipt-print-clone') {
                    hiddenElements.push({
                        element: child,
                        display: child.style.display,
                        visibility: child.style.visibility,
                        height: child.style.height
                    });
                    child.style.display = 'none';
                    child.style.visibility = 'hidden';
                    child.style.height = '0';
                }
            });
            
            // Append clone to body
            document.body.appendChild(receiptClone);
            
            // Small delay to ensure DOM is ready
            setTimeout(() => {
                // Trigger print
                window.print();
                
                // Clean up after printing
                setTimeout(() => {
                    receiptClone.remove();
                    hiddenElements.forEach(item => {
                        item.element.style.display = item.display;
                        item.element.style.visibility = item.visibility;
                        item.element.style.height = item.height;
                    });
                    document.body.style.height = originalBodyStyle.height;
                    document.body.style.overflow = originalBodyStyle.overflow;
                    document.body.style.margin = originalBodyStyle.margin;
                    document.body.style.padding = originalBodyStyle.padding;
                }, 500);
            }, 100);
        });
    });

    // Barcode scanner support
    let barcodeBuffer = '';
    let barcodeTimeout;

    document.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT' && e.target.type !== 'number') return;
        
        clearTimeout(barcodeTimeout);
        
        if (e.key === 'Enter' && barcodeBuffer.length > 0) {
            @this.call('scanBarcode', barcodeBuffer);
            barcodeBuffer = '';
            return;
        }
        
        if (e.key.length === 1) {
            barcodeBuffer += e.key;
        }
        
        barcodeTimeout = setTimeout(() => {
            barcodeBuffer = '';
        }, 100);
    });
</script>
@endpush
