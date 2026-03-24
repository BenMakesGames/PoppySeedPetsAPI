import { domToPng } from 'modern-screenshot';

/**
 * Tests that the screenshot library can convert a DOM element containing
 * @font-face CSS rules to a PNG without crashing. This exercises the font
 * embedding code path that regressed in html-to-image v1.11.12+ on Firefox.
 */
describe('take-picture download', () => {
  let container: HTMLDivElement;
  let style: HTMLStyleElement;

  beforeEach(() => {
    // Inject a @font-face rule so the library's font embedding code runs
    style = document.createElement('style');
    style.textContent = `
      @font-face {
        font-family: 'TestFont';
        src: url('data:font/woff2;base64,') format('woff2');
      }
    `;
    document.head.appendChild(style);

    container = document.createElement('div');
    container.style.width = '100px';
    container.style.height = '100px';
    container.style.fontFamily = "'TestFont', sans-serif";
    container.textContent = 'Hello';
    document.body.appendChild(container);
  });

  afterEach(() => {
    document.body.removeChild(container);
    document.head.removeChild(style);
  });

  it('should convert a DOM element with @font-face rules to PNG without error', async () => {
    const dataUrl = await domToPng(container);
    expect(dataUrl).toBeTruthy();
    expect(dataUrl.startsWith('data:image/png')).toBeTrue();
  });
});
