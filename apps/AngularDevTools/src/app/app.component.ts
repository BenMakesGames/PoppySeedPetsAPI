import { Component } from '@angular/core';
import { RouterLinkActive, RouterLink, RouterOutlet } from '@angular/router';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss'],
  standalone: true,
  imports: [RouterLinkActive, RouterLink, RouterOutlet]
})
export class AppComponent {
  title = 'PoppySeedPetsTools';
}
