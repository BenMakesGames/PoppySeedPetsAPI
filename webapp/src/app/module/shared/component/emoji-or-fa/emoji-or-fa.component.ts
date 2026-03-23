import { Component, input } from '@angular/core';

@Component({
    selector: 'app-emoji-or-fa',
    imports: [],
    templateUrl: './emoji-or-fa.component.html'
})
export class EmojiOrFaComponent {
  icon = input.required<string>();
}
