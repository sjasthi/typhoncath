using Microsoft.Playwright;
using NUnit.Framework;

namespace CRMTests;

public class LoginTests
{
    [Test]
    public async Task LoginWorks()
    {
        using var playwright = await Playwright.CreateAsync();

	await using var browser = await playwright.Chromium.LaunchAsync(
	    new BrowserTypeLaunchOptions
	    {
		Headless = false,
		SlowMo = 500
	    });

        var page = await browser.NewPageAsync();

        await page.GotoAsync("http://localhost:8080/login.php");

        await page.FillAsync("input[name=email]", "admin@typhoncath.test");
        await page.FillAsync("input[name=password]", "password");

        await page.ClickAsync("button[type=submit]");

        Assert.That(page.Url, Does.Contain("dashboard"));
    }
}
