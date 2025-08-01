# WordPress Development with Browsersync + Gulp

## Installation

1. **Clone/Download the Theme** to your local WordPress installation.

2. **Install Node.js** (If not installed already):  
   [Download Node.js](https://nodejs.org/)

3. **Install Dependencies**:  
   In your theme folder, open a terminal and run:

   ```bash
   npm install
   ```

4. **Configure Browsersync**:  
   In `gulpfile.js`, make sure to update the `proxy` with your **local WordPress site URL** (e.g., `http://localhost:8000`).

---

## Usage

1. **Start Development Server**:  
   In your terminal, run:

   ```bash
   npx gulp
   ```

2. **Automatic Refresh**:  
   Your browser will auto-refresh when you change PHP, CSS, or JS files in the theme.

---

## Additional Command

- **Sass Compilation**:  
   To compile Sass files, run:

   ```bash
   npm run sass
   ```

---

## Notes

- Ensure you have **WordPress** set up locally and running.
- **Browsersync** will auto-refresh your page for any changes made to theme files.
- The **Sass** command compiles `.scss` to `.css`.

---

### **Component-Based CSS**
- Introduced a method to **dynamically enqueue component-specific styles** (e.g., `header`, `footer`, `hero`) based on the current page. This optimizes performance by loading only the required CSS for each page.

### **Mobile Detect Library**
- Integrated the **MobileDetect library** to detect device types (e.g., mobile, tablet). This enables better device-specific customization, like responsive layouts or content adjustments.

---

These changes aim to optimize theme performance and improve responsiveness based on the user's device.
