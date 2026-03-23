import {
  Component, ElementRef, input, Input, OnChanges, SimpleChanges,
  ViewChild
} from '@angular/core';
import { DomSanitizer, SafeHtml } from "@angular/platform-browser";
import { HttpClient } from "@angular/common/http";

@Component({
  standalone: true,
  selector: 'app-image',
  template: `<div #imageContainer [innerHTML]="safeSvg" [style.width]="width()" [style.height]="height()" [title]="alt()"></div>`,
  styles: [
    ':host { width: 100%; }',
  ]
})
export class ImageComponent implements OnChanges {

  static svgCache: {[key:string]:string} = {};

  alt = input<string>('');
  width = input<string>('auto');
  height = input<string>('auto');
  @Input() path: string;
  @Input() colors: {[key:string]:string};

  @ViewChild('imageContainer', { 'static': true }) imageContainer: ElementRef;

  id = 'svg-' + Math.random().toString(36).substring(2);
  svg = '';
  safeSvg: SafeHtml = '';

  constructor(
    private sanitizer: DomSanitizer,
    private httpClient: HttpClient
  ) { }

  private generateStyleText(colors: {[key:string]:string})
  {
    const css = !colors ? '' : Object.keys(colors)
      .map(k => '#' + this.id + ' .' + k + ' { fill: #' + colors[k] + '; }')
      .join('\n')
    ;

    return `<style id="custom-color-css">
${css}
#${this.id} { display: block; margin: 0; position: relative; pointer-events: none; width: 100%; height: 100%; }
</style>`;
  }

  ngOnChanges(changes: SimpleChanges) {
    if(changes.path)
    {
      const path = changes.path.currentValue;

      if(ImageComponent.svgCache[path])
        this.buildSvg(ImageComponent.svgCache[path]);
      else
      {

        this.httpClient.get('/assets/images/' + path.replace(/[^a-z0-9\/'-]/, '') + '.svg', { responseType: 'text' })
          .subscribe(value => {

            ImageComponent.svgCache[path] = value;
            this.buildSvg(ImageComponent.svgCache[path]);
          });
      }
    }
    else if(changes.colors)
    {
      this.buildSvg(this.svg);
    }
  }

  // the hackiest shit ever:
  private buildSvg(svg: string)
  {
    const style = this.generateStyleText(this.colors);

    // remove the old <style> block, if any:
    svg = svg.replace(/<style id="custom-color-css">.*?<\/style>/s, '');

    // add a new style block
    this.svg = svg.replace(/<(svg[^>]*)>/, '<$1 id="' + this.id + '">' + style);

    this.safeSvg = this.sanitizer.bypassSecurityTrustHtml(this.svg);
  }
}
