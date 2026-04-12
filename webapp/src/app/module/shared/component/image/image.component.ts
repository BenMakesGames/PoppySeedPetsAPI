/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
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
