import {Injectable} from '@angular/core';
import {ApiService} from "../module/shared/service/api.service";
import {ApiResponseModel} from "../model/api-response.model";
import {UserDataService} from "./user-data.service";
import {Observable} from "rxjs";
import {MessagesService} from "./messages.service";
import { LogInSerializationGroup } from "../model/log-in.serialization-group";
import { ThemeService } from "../module/shared/service/theme.service";
import { ThemeInterface } from "../model/theme.interface";

@Injectable({
  providedIn: 'root'
})
export class UserSessionService {

  showTutorial = false;

  constructor(
    private api: ApiService, private userDataService: UserDataService,
    private messages: MessagesService, private theme: ThemeService
  ) {
    this.api.get<LogInSerializationGroup>('/account').subscribe({
      next: r => {
        this.theme.setTheme(r.data.currentTheme);
      },
      error: () => {
        this.userDataService.updateUser(null);
      },
    });
  }

  logOut()
  {
    this.api.post('/account/logOut').subscribe({
      next: _ => {
        this.userDataService.updateUser(null);
        this.messages.clearMessages();
      }
    });
  }

  logIn(email: string, passphrase: string, rememberMe: boolean): Observable<ApiResponseModel<LogInSerializationGroup>>
  {
    const data = {
      email: email,
      passphrase: passphrase,
      rememberMe: rememberMe
    };

    return this.api.post<LogInSerializationGroup>('/account/logIn', data);
  }

  register(
    theme: ThemeInterface,
    name: string, email: string, passphrase: string,
    petName: string, petGraphic: string, petColorA: string, petColorB: string
  ): Observable<ApiResponseModel<void>>
  {
    const data = {
      theme: theme,
      playerName: name,
      playerEmail: email,
      playerPassphrase: passphrase,
      petName: petName,
      petImage: petGraphic,
      petColorA: petColorA,
      petColorB: petColorB
    };

    return this.api.post<void>('/account/register', data);
  }
}
