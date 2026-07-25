using Microsoft.Playwright;
using NUnit.Framework;

namespace CRMTests;

public class CustomerCrudTests
{
    private IPlaywright playwright = null!;
    private IBrowser browser = null!;
    private IPage page = null!;


    [SetUp]
    public async Task Setup()
    {
        playwright = await Playwright.CreateAsync();

        browser = await playwright.Chromium.LaunchAsync(
            new BrowserTypeLaunchOptions
            {
                Headless = false,
                SlowMo = 500
            });

        page = await browser.NewPageAsync();

        await Login();
    }


    private async Task Login()
    {
        await page.GotoAsync(
            "http://localhost:8080/login.php");

        await page.FillAsync(
            "input[name=email]",
            "admin@typhoncath.test");

        await page.FillAsync(
            "input[name=password]",
            "password");

        await page.ClickAsync(
            "button[type=submit]");

        await page.WaitForLoadStateAsync();
    }


    [Test]
    public async Task Customer_CRUD_Test()
    {
        string customerName = 
            "Playwright Test Customer";

        string updatedName =
            "Updated Playwright Customer";


        // ==========================
        // CREATE CUSTOMER
        // ==========================

        await page.ClickAsync("text=Customers");

        // Click blue plus button
        await page.ClickAsync("a[href='create_account.php']");

        // Wait for create form
        await page.WaitForSelectorAsync("#create-entity-form");

        // Select Account
        await page.SelectOptionAsync(
            "#entity-type",
            "account"
        );

        // Fill fields
        await page.FillAsync(
            "#acc-name",
            customerName);

        await page.FillAsync(
            "#acc-email",
            "playwright@test.com");

        await page.FillAsync(
            "#acc-phone",
            "5555555555");

        // Submit
        await page.ClickAsync(
            "#create-submit");

        await page.WaitForLoadStateAsync();


        // ==========================
        // READ CUSTOMER
        // ==========================

        bool customerExists =
            await page
                .Locator($"text={customerName}")
                .IsVisibleAsync();


        Assert.That(
            customerExists,
            Is.True,
            "Created customer should appear");



        // ==========================
        // UPDATE CUSTOMER
        // ==========================

        await page.ClickAsync(
            $"text={customerName}");


        await page.ClickAsync(
            "text=Edit");


        await page.FillAsync(
            "input[name=account_name]",
            updatedName);


        await page.ClickAsync(
            "button[type=submit]");


        await page.WaitForLoadStateAsync();


        bool updatedCustomerExists =
            await page
                .Locator($"text={updatedName}")
                .IsVisibleAsync();


        Assert.That(
            updatedCustomerExists,
            Is.True,
            "Updated customer should appear");



        // ==========================
        // DELETE CUSTOMER
        // ==========================

        page.Dialog += async (_, dialog) =>
        {
            await dialog.AcceptAsync();
        };


        await page.ClickAsync(
            $"text={updatedName}");


        await page.ClickAsync(
            "text=Delete");


        await page.WaitForLoadStateAsync();



        // ==========================
        // VERIFY DELETE
        // ==========================

        bool customerDeleted =
            await page
                .Locator($"text={updatedName}")
                .IsVisibleAsync();


        Assert.That(
            customerDeleted,
            Is.False,
            "Deleted customer should no longer appear");
    }


    [TearDown]
    public async Task Cleanup()
    {
        await browser.CloseAsync();
        playwright.Dispose();
    }
}
