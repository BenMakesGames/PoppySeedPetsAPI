import { Component, Inject, LOCALE_ID, OnDestroy, OnInit } from '@angular/core';
import {
  getFeatureUnlockedDate,
  MyAccountSerializationGroup
} from "../../../../model/my-account/my-account.serialization-group";
import { Subscription } from "rxjs";
import { UserDataService } from "../../../../service/user-data.service";
import { ApiService } from "../../../shared/service/api.service";
import { formatDate } from "@angular/common";
import { MyInventorySerializationGroup } from "../../../../model/my-inventory/my-inventory.serialization-group";
import { MyStatsSerializationGroup } from "../../../../model/my-stats.serialization-group";
import { WeatherService } from "../../../shared/service/weather.service";
import { MatDialog } from "@angular/material/dialog";
import { FeedMonsterDialog } from "../../dialog/feed-monster/feed-monster.dialog";
import { ClaimRewardsDialog } from "../../dialog/claim-rewards/claim-rewards.dialog";
import { MonsterOfTheWeekModel } from "../../model/monster-of-the-week.model";
import { WeatherDataModel, WeatherSky } from "../../../../model/weather.model";

@Component({
    templateUrl: './plaza.component.html',
    styleUrls: ['./plaza.component.scss'],
    standalone: false
})
export class PlazaComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Plaza' };

  DialogStepEnum = DialogStepEnum;

  helloDialogFirstLine = '';
  allRelevantHolidays = [];
  step = DialogStepEnum.Loading;
  askingAboutHoliday = '';
  nextCollectionDay: string;
  canGetHandicraftsBox = false;
  canGetFishBag = false;
  canGetGamingBox = false;

  user: MyAccountSerializationGroup;
  userSubscription: Subscription;
  userShortName: string;

  weatherSubscription = Subscription.EMPTY;
  month: number;
  day: number;
  pickingBox = false;

  holidayBoxes: string[] = [];

  monsterOfTheWeek: MonsterOfTheWeekModel|null = null;

  mainClass = '';
  tessImage = '/assets/images/npcs/tess-standing.svg';
  statsAjax: Subscription;

  constructor(
    private userData: UserDataService, private api: ApiService, @Inject(LOCALE_ID) private locale,
    private weatherService: WeatherService, private matDialog: MatDialog
  ) { }

  ngOnInit()
  {
    this.statsAjax = this.api.get<MyStatsSerializationGroup[]>('/account/stats').subscribe(r => {
      const museumStat = r.data.find(s => s.stat === 'Items Donated to Museum');
      const itemsDonated = museumStat ? museumStat.value : 0;

      let lastWeek = new Date();
      lastWeek.setDate(lastWeek.getDate() - 7);

      this.user = this.userData.user.getValue();
      this.userShortName = this.userData.getUserShortName();

      this.canGetGamingBox = !!getFeatureUnlockedDate(this.user, 'Hollow Earth');
      this.canGetHandicraftsBox = itemsDonated >= 150;
      this.canGetFishBag = itemsDonated >= 450;

      this.step = DialogStepEnum.Hello;
    });

    this.userSubscription = this.userData.user.subscribe({
      next: u => {
        this.user = u;
      }
    });

    this.weatherSubscription = this.weatherService.weather.subscribe({
      next: weather => {
        if(!weather) return;

        this.updateTime(weather);

        const today = weather?.find(w => new Date().toISOString().startsWith(w.date));
        this.mainClass = today.sky;

        if(today.sky === WeatherSky.Stormy || today.sky == WeatherSky.Rainy)
          this.tessImage = '/assets/images/npcs/tess-standing-poncho.svg';
        else
          this.tessImage = '/assets/images/npcs/tess-standing.svg';
      }
    });
  }

  ngOnDestroy()
  {
    this.userSubscription.unsubscribe();
    this.statsAjax.unsubscribe();
  }

  updateTime(weather: WeatherDataModel[])
  {
    const now = new Date();

    const todayDay = formatDate(now, 'EEEE', this.locale, 'UTC');
    const collectionDay = formatDate(this.user.lastAllowanceCollected, 'EEEE', this.locale, 'UTC');

    if(todayDay === collectionDay)
      this.nextCollectionDay = 'next ' + collectionDay;
    else
      this.nextCollectionDay = collectionDay;

    this.helloDialogFirstLine = 'Hey, ' + this.user.name  + '. How\'s it going?';

    this.computeAllRelevantHolidays(weather);
    this.getHolidayBoxes();
    this.getMonster();
  }

  public doReturnToHello()
  {
    this.step = DialogStepEnum.Hello;
    this.helloDialogFirstLine = 'Anything else I can do for you?';
  }

  private computeAllRelevantHolidays(weather: WeatherDataModel[]|null)
  {
    const today = weather?.find(w => new Date().toISOString().startsWith(w.date));
    const todaysHolidays = today ? today.holidays : [];

    this.allRelevantHolidays = [ ...todaysHolidays ];

    const holidays = weather
      ?.reduce((list, f) => {
        list.push(...f.holidays);
        return list;
      }, [])
      .filter((value, index, self) => self.indexOf(value) === index) // filter out duplicates
      .filter(v => todaysHolidays.indexOf(v) < 0) // filter out copies of today's holiday
      ?? []
    ;

    this.allRelevantHolidays.push(...holidays);
  }

  getHolidayBoxes()
  {
    this.api.get<{ holidayBoxes: string[] }>('/plaza/holidayBoxes').subscribe({
      next: v => {
        this.holidayBoxes = v.data.holidayBoxes;
      }
    });
  }

  getMonster()
  {
    this.api.get<MonsterOfTheWeekModel>('/monsterOfTheWeek/current').subscribe({
      next: v => {
        this.monsterOfTheWeek = v.data;
      }
    });
  }

  doFeedMonster()
  {
    FeedMonsterDialog.open(this.matDialog, this.monsterOfTheWeek).afterClosed().subscribe({
      next: progress => {
        if(progress)
        {
          this.monsterOfTheWeek.personalContribution = progress.personalContribution;
          this.monsterOfTheWeek.communityTotal = progress.communityTotal;
        }
      }
    });
  }

  doCollect()
  {
    ClaimRewardsDialog.open(this.matDialog);
  }

  doAskAboutCarePackage()
  {
    let lastWeek = new Date();
    lastWeek.setDate(lastWeek.getDate() - 7);

    if((new Date(this.user.lastAllowanceCollected)) <= lastWeek)
      this.step = DialogStepEnum.GetBox;
    else
      this.step = DialogStepEnum.AskAboutBoxTime;
  }

  doAskAboutPark()
  {
    this.step = DialogStepEnum.AskAboutPark;
  }

  doAskAboutMoneys()
  {
    this.step = DialogStepEnum.AskAboutMoneys;
  }

  doAskAboutHoliday(holiday: string)
  {
    this.step = DialogStepEnum.AskAboutHoliday;
    this.askingAboutHoliday = holiday;
  }

  doGetHolidayBox(box: string)
  {
    this.api.post<string[]>('/plaza/collectHolidayBox', { box: box }).subscribe({
      next: v => {
        this.holidayBoxes = v.data;
        this.step = DialogStepEnum.Hello;
        this.helloDialogFirstLine = 'Here you go! Have fun!';
      },
    });
  }

  doPickCarePackage(choice: number)
  {
    if(this.pickingBox) return;

    this.pickingBox = true;

    this.api.post<MyInventorySerializationGroup>('/plaza/collectWeeklyCarePackage', { type: choice }).subscribe({
      next: () => {
        this.step = DialogStepEnum.Hello;
        this.helloDialogFirstLine = 'Here you go! See you next week!';
        this.pickingBox = false;
      },
      error: () => {
        this.pickingBox = false;
      }
    });
  }

}

enum DialogStepEnum
{
  Loading,
  GetBox,
  GotBox,
  Hello,
  AskAboutBoxTime,
  AskAboutPark,
  AskAboutMoneys,
  AskAboutHoliday,
  AskAboutMonster,
  AskAboutMonsterTypes,
  AskAboutVafAndNir
}
