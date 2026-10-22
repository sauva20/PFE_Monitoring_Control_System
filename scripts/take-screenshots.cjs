const puppeteer = require('puppeteer');

(async () => {
    console.log('Starting puppeteer...');
    const browser = await puppeteer.launch({ 
        headless: 'new',
        executablePath: 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe' 
    });
    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 800 });

    const baseUrl = 'http://localhost:8001';

    try {
        // 1. Login Page
        await page.goto(`${baseUrl}/`, { waitUntil: 'networkidle2' });
        await page.screenshot({ path: 'docs/images/1_login.png' });
        console.log('Saved 1_login.png');

        // Login
        await page.type('input[name="email"]', 'head@ehss.id');
        await page.type('input[name="password"]', 'password');
        
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
            page.click('button[type="submit"]')
        ]);
        // wait a bit for animations
        await new Promise(r => setTimeout(r, 2000));

        // 2. Dashboard
        await page.screenshot({ path: 'docs/images/2_dashboard.png', fullPage: true });
        console.log('Saved 2_dashboard.png');

        // 3. Master Data (Apar)
        await page.goto(`${baseUrl}/master-data`, { waitUntil: 'domcontentloaded' });
        await new Promise(r => setTimeout(r, 1000));
        await page.screenshot({ path: 'docs/images/3_master_data.png', fullPage: true });
        console.log('Saved 3_master_data.png');

        // 4. Jadwal Inspeksi
        await page.goto(`${baseUrl}/inspection-schedule`, { waitUntil: 'domcontentloaded' });
        await new Promise(r => setTimeout(r, 1000));
        await page.screenshot({ path: 'docs/images/4_jadwal_inspeksi.png', fullPage: true });
        console.log('Saved 4_jadwal_inspeksi.png');

        // 5. Riwayat Inspeksi
        await page.goto(`${baseUrl}/reports`, { waitUntil: 'domcontentloaded' });
        await new Promise(r => setTimeout(r, 1000));
        await page.screenshot({ path: 'docs/images/5_riwayat_inspeksi.png', fullPage: true });
        console.log('Saved 5_riwayat_inspeksi.png');

        console.log('All screenshots saved successfully.');
    } catch (e) {
        console.error('Error during screenshots:', e);
    } finally {
        await browser.close();
    }
})();
