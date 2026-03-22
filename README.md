# Kueue Events Core

A comprehensive, production-grade event ticketing, booking, and marketplace plugin for WordPress.

## ✨ Overview

Kueue Events Core is designed to transform any WordPress site into a fully capable event and ticketing marketplace. Built with modularity, scaling, and modern aesthetics in mind, it provides both organizers and administrators with powerful tools to manage events, ticket types, attendees, and earnings.

## 🚀 Features at a Glance

### Admin Layer
- **Dashboard**: High-level overview of system metrics.
- **Events Management**: Custom meta boxes for schedules, venues, and ticket rules.
- **Ticket Types**: Granular control over pricing, stock, and ordering limits.
- **POS / Box Office**: AJAX-driven, specialized interface for manual ticket issuance and attendee creation.
- **Seating & Bookings**: Integrated time-slot management and venue seating map tools.
- **Check-in logs**: Track attendee check-ins in real-time.
- **Reports & Finance**: See global revenue, track pending payouts, and review organizer commissions.

### Frontend Layer
- **Organizer Dashboard** (`[kq_dashboard]`): A robust UI for logged-in organizers to view stats, manage events, and track ticket sales.
- **Event Listings & Single Pages** (`[kq_events]`, `[kq_event]`): AJAX-powered ticket selection seamlessly connected to the cart.
- **GDPR Tools**: Native integration with WordPress core data export and erasure tools.

### Core Architecture
- **WooCommerce Integration**: Event tickets are automatically synced as virtual products. Orders dynamically trigger ticket and attendee creation.
- **PDF & QR Code Engine**: Automated ticket generation with PDF formatting and embeddable QR codes (with local fallbacks).
- **Communication Gateways**: Support for extending the delivery mechanism via SMS and WhatsApp API providers.
- **Native GitHub Updater**: Fully integrated with WordPress core. Allows 1-click updates directly from the GitHub repository, complete with caching, secure transients, and a "View Details" modal just like official repository plugins.
- **Case-Sensitive PSR-4 Autoloader**: Designed to run cleanly across strict Unix/Linux servers without namespace collisions.

## 📁 Directory Structure

```text
kueue-events-core/
├── kueue-events-core.php    # Main plugin file & Autoloader
├── assets/                  # CSS, JS, and Images
└── includes/
    ├── Admin/               # Global Admin UI & Routing
    ├── API/                 # REST API Handlers
    ├── Core/                # Bootstrap, Updater, Ajax Handling
    ├── Helpers/             # Utility Functions
    ├── Modules/             # Domain-specific logic and isolated views
    │   ├── Attendees/
    │   ├── Bookings/
    │   ├── Checkins/
    │   ├── Dashboard/
    │   ├── Delivery/
    │   ├── Events/
    │   ├── Finance/
    │   ├── Frontend/
    │   ├── Gateways/
    │   ├── POS/
    │   ├── Payments/
    │   ├── Reports/
    │   ├── Seating/
    │   ├── Tickets/
    │   └── Vendors/
    └── Vendor/              # 3rd-party libraries & fallbacks
```

## 🛠️ Installation & Updating

Kueue Events Core supports intelligent self-updating directly from GitHub.
1. Download the latest stable Release `.zip` from the repository.
2. Upload the `.zip` via `Plugins > Add New` in the WordPress admin panel.
3. Activate the plugin.

Whenever a new stable release is tagged in the repository, it will surface natively in `Dashboard > Updates`.

## 🚧 Current Status / Next Steps

The backend logic and UI layer are solidly in place. To pick up where we left off:
1. **Frontend Styling**: Refine the visual design matching the brand guidelines.
2. **Third-Party Gateways**: Connect specific third-party APIs into the standardized SMS/WhatsApp interfaces.
3. **Advanced Reporting Charts**: Load charting libraries in the admin Reports module for visual metrics.
