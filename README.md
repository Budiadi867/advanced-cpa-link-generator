
Built by https://www.blackbox.ai

---

# Advanced CPA Link Generator

## Project Overview

The Advanced CPA Link Generator is a PHP-based application designed to generate protected links for Cost Per Action (CPA) campaigns. It simulates mobile devices, manages Facebook-specific parameters, and provides a user-friendly interface to create, manage, and track personalized smart links for different campaigns.

## Installation

To install the project, follow these steps:

1. **Clone the repository**:
   ```bash
   git clone https://github.com/yourusername/advanced-cpa-link-generator.git
   cd advanced-cpa-link-generator
   ```

2. **Set up a local server**:
   Ensure you have a web server (like Apache or Nginx) with PHP support. You can use tools like XAMPP or MAMP to set this up.

3. **Configure the environment**:
   Place the project files into the server's document root. Ensure the `uploads` directory is writable by the web server:
   ```bash
   mkdir uploads
   chmod 755 uploads
   ```

4. **Database setup**:
   If your application requires a database setup, ensure to create a database and update the configuration settings accordingly (not implemented directly in this project).

5. **Run the application**:
   Open a web browser and navigate to the URL where you hosted the application, e.g., `http://localhost/advanced-cpa-link-generator/index.html`.

## Usage

- Open the `index.html` page in a web browser.
- Fill in the required fields, including the title, description, and image URL for your CPA link.
- Select the protection features you wish to apply.
- Click the **Generate Protected Link** button.
- Your encoded link will display, and you'll have the option to copy it to your clipboard.

## Features

- **Mobile Device Simulation**: Simulates various mobile devices to ensure proper tracking and user engagement.
- **Facebook App Protection**: Sets the necessary headers for Facebook app compatibility.
- **Browser Fingerprint Rotation**: Prevents tracking via browser fingerprints to ensure user privacy.
- **User-friendly Interface**: Easy-to-use form for generating smart links.
- **Image Management**: Upload and manage images for your campaigns within the application.

## Dependencies

No external dependencies are declared in the project, but it relies on standard PHP functions for runtime.

## Project Structure

```
/advanced-cpa-link-generator
│
├── index.html             # User interface for link generation
├── mobile_protection.php  # PHP class for mobile device protection logic
├── redirect.php           # Handling redirects and link protection
├── generate.php           # Link generation logic and response handling
├── verify.php             # Verification for bot protection
├── verify_challenge.php    # Challenge response for verifying user session
├── image_proxy.php        # Proxy for serving uploaded images
├── image_manager.html     # UI for managing image uploads
├── upload_image.php       # Logic for handling image uploads
├── list_images.php        # Endpoint for listing uploaded images
├── clicks.json            # JSON file for storing click data
├── links.json             # JSON file for storing generated link data
├── tier3_countries.json   # List of countries for geo-targeting
└── uploads/               # Directory for storing uploaded images
```

## License

This project is open-source and available for use. Feel free to contribute by forking the repository and creating pull requests.

---

If you have any questions or issues while using the application, please feel free to create an issue in the repository. Happy generating!