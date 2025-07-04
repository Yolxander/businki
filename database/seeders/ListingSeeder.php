<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Listing;

class ListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $listings = [
            [
                "slug" => "business-landing-page",
                "title" => "Business Landing Page",
                "industry" => "General Business",
                "type" => "website",
                "featured" => true,
                "image" => "https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=600&q=80",
                "description" => "A simple, one-page website that clearly showcases your business services, contact details, and calls to action. Perfect for getting your business online quickly.",
                "frames" => [
                    "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=400&q=80"
                ],
                "features" => [
                    "Clear service showcase",
                    "Contact information",
                    "Call-to-action buttons",
                    "Mobile-friendly design"
                ],
                "services" => [
                    "1-month basic support",
                    "Domain & hosting setup",
                    "Basic SEO optimization",
                    "Contact form setup"
                ],
                "price" => "$299",
                "demo" => "#"
            ],
            [
                "slug" => "restaurant-cafe-website",
                "title" => "Restaurant & Cafe Website",
                "industry" => "Food & Beverage",
                "type" => "website",
                "featured" => true,
                "image" => "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=600&q=80",
                "description" => "Complete restaurant website with menus, reservations, ordering features, hours, location, and photo gallery. Everything your customers need in one place.",
                "frames" => [
                    "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=400&q=80"
                ],
                "features" => [
                    "Online menu display",
                    "Reservation system",
                    "Ordering integration",
                    "Photo gallery"
                ],
                "services" => [
                    "Menu setup & updates",
                    "Reservation system setup",
                    "Photo gallery creation",
                    "1-month support"
                ],
                "price" => "$399",
                "demo" => "#"
            ],
            [
                "slug" => "portfolio-resume-website",
                "title" => "Portfolio or Resume Website",
                "industry" => "Freelancers & Professionals",
                "type" => "website",
                "featured" => false,
                "image" => "https://images.unsplash.com/photo-1467232004584-a241de8bcf5d?auto=format&fit=crop&w=600&q=80",
                "description" => "Minimalist portfolio page highlighting your professional skills, projects, or services. Perfect for freelancers, consultants, and creative professionals.",
                "frames" => [
                    "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=400&q=80"
                ],
                "features" => [
                    "Project showcase",
                    "Skills & experience",
                    "Contact section",
                    "Clean, professional design"
                ],
                "services" => [
                    "Content organization",
                    "Professional design",
                    "Domain setup",
                    "1-month support"
                ],
                "price" => "$249",
                "demo" => "#"
            ],
            [
                "slug" => "ecommerce-website",
                "title" => "E-Commerce Website",
                "industry" => "Retail & Sales",
                "type" => "website",
                "featured" => false,
                "image" => "https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=600&q=80",
                "description" => "Basic online store for selling physical or digital products with intuitive product management and secure payment options.",
                "frames" => [
                    "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=400&q=80"
                ],
                "features" => [
                    "Product catalog",
                    "Shopping cart",
                    "Secure checkout",
                    "Order management"
                ],
                "services" => [
                    "Product upload assistance",
                    "Payment gateway setup",
                    "Inventory management",
                    "1-month support"
                ],
                "price" => "$599",
                "demo" => "#"
            ],
            [
                "slug" => "booking-appointment-website",
                "title" => "Booking & Appointment Website",
                "industry" => "Service Providers",
                "type" => "website",
                "featured" => false,
                "image" => "https://images.unsplash.com/photo-1506784365847-bbad939e9335?auto=format&fit=crop&w=600&q=80",
                "description" => "Allow clients to book appointments directly online. Perfect for salons, clinics, consultants, and any service-based business.",
                "frames" => [
                    "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=400&q=80"
                ],
                "features" => [
                    "Online booking calendar",
                    "Service selection",
                    "Email confirmations",
                    "Calendar integration"
                ],
                "services" => [
                    "Booking system setup",
                    "Calendar integration",
                    "Email notifications",
                    "1-month support"
                ],
                "price" => "$349",
                "demo" => "#"
            ],
            [
                "slug" => "real-estate-website",
                "title" => "Real Estate Website",
                "industry" => "Real Estate",
                "type" => "website",
                "featured" => false,
                "image" => "https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=600&q=80",
                "description" => "Showcase property listings, details, galleries, and provide easy ways for potential buyers to contact agents.",
                "frames" => [
                    "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=400&q=80"
                ],
                "features" => [
                    "Property listings",
                    "Photo galleries",
                    "Contact forms",
                    "Search functionality"
                ],
                "services" => [
                    "Listing setup",
                    "Photo gallery creation",
                    "Contact form setup",
                    "1-month support"
                ],
                "price" => "$449",
                "demo" => "#"
            ],
            [
                "slug" => "event-website",
                "title" => "Event Website",
                "industry" => "Events & Entertainment",
                "type" => "website",
                "featured" => false,
                "image" => "https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=600&q=80",
                "description" => "Promote your event with ticket sales, registration forms, and all event details in an accessible, user-friendly format.",
                "frames" => [
                    "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=400&q=80"
                ],
                "features" => [
                    "Event details",
                    "Ticket sales",
                    "Registration forms",
                    "Countdown timer"
                ],
                "services" => [
                    "Event page setup",
                    "Registration form",
                    "Payment integration",
                    "1-month support"
                ],
                "price" => "$299",
                "demo" => "#"
            ],
            [
                "slug" => "online-menu-tool",
                "title" => "Online Menu Tool",
                "industry" => "Restaurants & Cafes",
                "type" => "tool",
                "featured" => false,
                "image" => "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=600&q=80",
                "description" => "Create a beautiful online menu that customers can access instantly. No app downloads required - just share a link.",
                "frames" => [
                    "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=400&q=80"
                ],
                "features" => [
                    "Mobile-friendly menu",
                    "Easy updates",
                    "Shareable link",
                    "No app required"
                ],
                "services" => [
                    "Menu setup",
                    "QR code generation",
                    "Basic analytics",
                    "Update training"
                ],
                "price" => "$149",
                "demo" => "#"
            ],
            [
                "slug" => "appointment-booking-tool",
                "title" => "Appointment Booking Tool",
                "industry" => "Service Providers",
                "type" => "tool",
                "featured" => true,
                "image" => "https://images.unsplash.com/photo-1506784365847-bbad939e9335?auto=format&fit=crop&w=600&q=80",
                "description" => "Simple appointment booking tool that works on any website. Let clients book appointments online with automatic confirmations.",
                "frames" => [
                    "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=400&q=80"
                ],
                "features" => [
                    "Online calendar",
                    "Email confirmations",
                    "Works on any site",
                    "No monthly fees"
                ],
                "services" => [
                    "Setup & integration",
                    "Email notifications",
                    "Calendar sync",
                    "Training included"
                ],
                "price" => "$199",
                "demo" => "#"
            ],
            [
                "slug" => "invoice-tool",
                "title" => "Invoice Tool",
                "industry" => "Freelancers & Small Business",
                "type" => "software",
                "featured" => false,
                "image" => "https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=600&q=80",
                "description" => "Create professional invoices, track payments, and manage your business finances with this simple invoicing solution.",
                "frames" => [
                    "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=400&q=80"
                ],
                "features" => [
                    "Branded invoices",
                    "Payment tracking",
                    "PDF downloads",
                    "No subscription"
                ],
                "services" => [
                    "Branding setup",
                    "Payment integration",
                    "Template customization",
                    "Training included"
                ],
                "price" => "$249",
                "demo" => "#"
            ],
            [
                "slug" => "feedback-collector",
                "title" => "Feedback Collector",
                "industry" => "Service Providers",
                "type" => "tool",
                "featured" => false,
                "image" => "https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=600&q=80",
                "description" => "Collect valuable feedback from your clients with a professional form. Customize branding and view results in a simple dashboard.",
                "frames" => [
                    "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=400&q=80"
                ],
                "features" => [
                    "Branded forms",
                    "Auto thank you messages",
                    "Simple dashboard",
                    "No technical skills needed"
                ],
                "services" => [
                    "Form customization",
                    "Email setup",
                    "Dashboard training",
                    "Support included"
                ],
                "price" => "$199",
                "demo" => "#"
            ],
            [
                "slug" => "quote-request-tool",
                "title" => "Quote Request Tool",
                "industry" => "Service Businesses",
                "type" => "tool",
                "featured" => false,
                "image" => "https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=600&q=80",
                "description" => "Convert website visitors into leads with a professional quote request form. Collect project details and receive instant notifications.",
                "frames" => [
                    "https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=400&q=80",
                    "https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=400&q=80"
                ],
                "features" => [
                    "Quote request form",
                    "Email notifications",
                    "Embed anywhere",
                    "Easy setup"
                ],
                "services" => [
                    "Form customization",
                    "Email configuration",
                    "Embedding assistance",
                    "Training included"
                ],
                "price" => "$149",
                "demo" => "#"
            ]
        ];

        foreach ($listings as $listing) {
            Listing::create($listing);
        }
    }
}
