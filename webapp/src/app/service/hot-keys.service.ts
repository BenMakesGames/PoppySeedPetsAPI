/*
 * This file is part of the Poppy Seed Pets Webapp.
 *
 * The Poppy Seed Pets Webapp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets Webapp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets Webapp. If not, see <https://www.gnu.org/licenses/>.
 */
import { Inject, Injectable, DOCUMENT } from '@angular/core';
import { EventManager } from "@angular/platform-browser";
import { Observable } from "rxjs";


@Injectable({
  providedIn: 'root'
})
export class HotKeysService {
  constructor(private eventManager: EventManager,
    @Inject(DOCUMENT) private document: Document) {
  }

  addShortcut(key: string) {
    // Support "code:" prefix for matching on physical key position (event.code)
    // instead of produced character (event.key). Useful for Shift+number shortcuts
    // that produce different characters on different keyboard layouts.
    if (key.startsWith('code:')) {
      return this.addCodeShortcut(key.substring(5));
    }

    const event = `keydown.${key}`;

    return new Observable(observer => {
      const handler = (e) => {
        if(/textarea|select|input/i.test(e.target.tagName))
          return;
        e.preventDefault();
        observer.next(e);
      };

      const dispose = this.eventManager.addEventListener(
        this.document.documentElement, event, handler
      );

      return () => {
        dispose();
      };
    });
  }

  private addCodeShortcut(key: string) {
    const parts = key.split('.');
    const code = parts.pop();
    const modifiers = new Set(parts.map(p => p.toLowerCase()));

    return new Observable(observer => {
      const handler = (e: KeyboardEvent) => {
        if (/textarea|select|input/i.test((e.target as HTMLElement).tagName))
          return;
        if (e.code !== code)
          return;
        if (modifiers.has('shift') !== e.shiftKey)
          return;
        if (modifiers.has('alt') !== e.altKey)
          return;
        if (modifiers.has('control') !== e.ctrlKey)
          return;
        if (modifiers.has('meta') !== e.metaKey)
          return;

        e.preventDefault();
        observer.next(e);
      };

      const dispose = this.eventManager.addEventListener(
        this.document.documentElement, 'keydown', handler
      );

      return () => {
        dispose();
      };
    });
  }
}