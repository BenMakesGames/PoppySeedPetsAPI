import {Component, OnDestroy, OnInit} from '@angular/core';
import {ApiService} from "../../../shared/service/api.service";
import {UserDataService} from "../../../../service/user-data.service";
import {MyAccountSerializationGroup} from "../../../../model/my-account/my-account.serialization-group";
import {ApiResponseModel} from "../../../../model/api-response.model";
import {PlantNewPlantDialog} from "../../dialog/plant-new-plant/plant-new-plant.dialog";
import {GreenhousePlantSerializationGroup} from "../../../../model/greenhouse/greenhouse-plant.serialization-group";
import {CareForPlantDialog} from "../../dialog/care-for-plant/care-for-plant.dialog";
import {MessagesService} from "../../../../service/messages.service";
import {AreYouSureDialog} from "../../../../dialog/are-you-sure/are-you-sure.dialog";
import {MyGreenhouseSerializationGroup} from "../../../../model/greenhouse/my-greenhouse.serialization-group";
import {GreenhousePlantTypeEnum} from "../../../../model/greenhouse-plant-type.enum";
import {Subscription} from "rxjs";
import {FeedComposterDialog} from "../../dialog/feed-composter/feed-composter.dialog";
import { FertilizerSerializationGroup } from "../../../../model/fertilizer.serialization-group";
import { SelectPetDialog } from "../../../../dialog/select-pet/select-pet.dialog";
import { InteractWithAwayPetDialog } from "../../../pet-helpers/dialog/interact-with-away-pet/interact-with-away-pet-dialog.component";
import { MatDialog } from "@angular/material/dialog";
import {ItemOtherPropertiesIcons} from "../../../../model/item-other-properties-icons";
import { CurrentMoonPhaseComponent } from "../../../shared/component/current-moon-phase/current-moon-phase.component";

@Component({
    selector: 'app-greenhouse',
    templateUrl: './greenhouse.component.html',
    styleUrls: ['./greenhouse.component.scss'],
    standalone: false
})
export class GreenhouseComponent implements OnInit, OnDestroy {
  pageMeta = { title: 'Greenhouse' };

  readonly GreenhousePlantTypeEnum = GreenhousePlantTypeEnum;
  readonly itemOtherPropertiesIcons = ItemOtherPropertiesIcons;

  lastMovedPlant: number;
  greenhouse: MyGreenhouseSerializationGroup;
  mode = 'tend';
  waitingOnAJAX = false;
  weeds: string|null = null;
  allPlants: GreenhousePlantSerializationGroup[];
  earthPlants: GreenhousePlantSerializationGroup[];
  waterPlants: GreenhousePlantSerializationGroup[];
  darkPlants: GreenhousePlantSerializationGroup[];
  fertilizer: FertilizerSerializationGroup[];
  user: MyAccountSerializationGroup;
  canInteract: any = {};
  hasBubblegum = false;
  hasOil = false;

  greenhouseAjax = Subscription.EMPTY;

  constructor(
    private api: ApiService, private userDataService: UserDataService, private matDialog: MatDialog,
    private messages: MessagesService
  ) { }

  ngOnInit() {
    this.userDataService.user.subscribe(u => {
      this.user = u;
    });

    this.loadPlants();
  }

  ngOnDestroy(): void {
    this.greenhouseAjax.unsubscribe();
  }

  doWeed()
  {
    if(this.waitingOnAJAX) return;

    this.waitingOnAJAX = true;

    this.api.post<string>('/greenhouse/weed').subscribe({
      next: (r) => {
        this.weeds = null;
        this.waitingOnAJAX = false;

        this.messages.addGenericMessage(r.data);
      },
      error: () => {
        this.waitingOnAJAX = false;
      }
    });
  }

  private loadPlants()
  {
    if(!this.greenhouseAjax.closed)
      this.greenhouseAjax.unsubscribe();

    this.waitingOnAJAX = true;

    this.greenhouseAjax = this.api.get<GreenhouseResponse>('/greenhouse').subscribe({
      next: (r: ApiResponseModel<GreenhouseResponse>) => {
        this.processGreenhouseResponse(r.data);
        this.waitingOnAJAX = false;
      },
      error: () => {
        this.waitingOnAJAX = false;
      }
    });
  }

  private processGreenhouseResponse(data: GreenhouseResponse)
  {
    if(!data)
      return;

    this.greenhouse = data.greenhouse;
    this.weeds = data.weeds;
    this.fertilizer = data.fertilizer;
    this.hasBubblegum = data.hasBubblegum;
    this.hasOil = data.hasOil;

    this.allPlants = data.plants.sort(this.plantSort);
    this.buildIndividualPlantLists();

    this.buildCanInteractMap();
  }

  plantSort = (a, b) => a.ordinal - b.ordinal;

  private buildIndividualPlantLists()
  {
    this.earthPlants = this.allPlants.filter(p => p.plant.type === GreenhousePlantTypeEnum.Earth);
    this.waterPlants = this.allPlants.filter(p => p.plant.type === GreenhousePlantTypeEnum.Water);
    this.darkPlants = this.allPlants.filter(p => p.plant.type === GreenhousePlantTypeEnum.Dark);
  }

  private buildCanInteractMap()
  {
    const now = Date.now();

    this.allPlants.forEach(p => {
      this.canInteract[p.id] = Date.parse(p.canNextInteract) < now;
    });
  }

  doAddPlant(type: GreenhousePlantTypeEnum)
  {
    PlantNewPlantDialog.open(this.matDialog, type).afterClosed().subscribe(r => {
      if(r && 'greenhouse' in r)
        this.processGreenhouseResponse(r.greenhouse);
    });
  }

  doTouchPlant(plant: GreenhousePlantSerializationGroup)
  {
    if(this.mode === 'tend')
      this.doCareForPlant(plant);
    else if(this.mode === 'pull-up')
      this.doPullUpPlant(plant);
  }

  doPullUpPlant(plant: GreenhousePlantSerializationGroup)
  {
    if(this.waitingOnAJAX) return;

    AreYouSureDialog.open(this.matDialog, 'Really Pull Up the ' + plant.plant.name + ' Plant?', 'You know I have to ask. Just in case.', 'Yep! Pull it up!', 'No, no, no!')
      .afterClosed()
      .subscribe({
        next: (confirmed: boolean) => {
          if(confirmed)
          {
            this.waitingOnAJAX = true;
            this.api.post('/greenhouse/' + plant.id + '/pullUp').subscribe({
              next: () => {
                this.allPlants = this.allPlants.filter(p => p.id !== plant.id);
                this.buildIndividualPlantLists();
                this.buildCanInteractMap();
                this.waitingOnAJAX = false;
              },
              error: () => {
                this.waitingOnAJAX = false;
              }
            });
          }
        }
      })
    ;
  }

  harvestPlant(plant: GreenhousePlantSerializationGroup)
  {
    this.waitingOnAJAX = true;

    this.api.post<GreenhouseResponse>('/greenhouse/' + plant.id + '/harvest').subscribe({
      next: r => {
        this.processGreenhouseResponse(r.data);
        this.waitingOnAJAX = false;
      },
      error: () => {
        this.waitingOnAJAX = false;
      }
    });
  }

  shouldConfirmHarvest(plant: GreenhousePlantSerializationGroup)
  {
    const moonPhase = CurrentMoonPhaseComponent.getMoonPhase(new Date());

    if(moonPhase === 'full')
      return plant.plant.name === 'Barnacle Tree' || plant.plant.name === 'Tomato Plant';
    else if(moonPhase === 'new')
      return plant.plant.name === 'Toadstool Troop';

    return false;
  }

  doCareForPlant(plant: GreenhousePlantSerializationGroup)
  {
    if(!this.canInteract[plant.id]) return;

    if(this.waitingOnAJAX) return;

    if(plant.isAdult && plant.progress >= 1)
    {
      if(this.greenhouse.hasMoondial && this.shouldConfirmHarvest(plant))
      {
        AreYouSureDialog.open(this.matDialog, 'Really harvest the ' + plant.plant.name + '?', 'The Moondial vibrates warily.', 'EMBRACE THE POWER OF THE MOON!', 'nm, actually')
          .afterClosed()
          .subscribe({
            next: (confirmed: boolean) => {
              if(confirmed)
              {
                this.harvestPlant(plant);
              }
            }
          });
      }
      else
      {
        this.harvestPlant(plant);
      }

      return;
    }

    if(!this.fertilizer || this.fertilizer.length <= 0)
      return;

    CareForPlantDialog.open(this.matDialog, plant.id, this.fertilizer)
      .afterClosed()
      .subscribe(r => {
        if(r && 'greenhouse' in r)
          this.processGreenhouseResponse(r.greenhouse);
      })
    ;
  }

  doClean()
  {
    if(this.waitingOnAJAX) return;

    this.waitingOnAJAX = true;

    this.api.post('/greenhouse/cleanBirdBath').subscribe({
      next: _ => {
        this.hasOil = false;
        this.hasBubblegum = false;
        this.waitingOnAJAX = false;
      },
      error: () => {
        this.waitingOnAJAX = false;
      }
    });
  }

  doTalkToBird()
  {
    if(this.waitingOnAJAX) return;

    this.waitingOnAJAX = true;

    this.api.post('/greenhouse/talkToVisitingBird').subscribe({
      next: () => {
        this.greenhouse.visitingBird = null;
        this.waitingOnAJAX = false;
      },
      error: () => {
        this.waitingOnAJAX = false;
      }
    });
  }

  doFeedComposter()
  {
    if(this.waitingOnAJAX) return;

    this.waitingOnAJAX = true;

    FeedComposterDialog.open(this.matDialog, this.fertilizer).afterClosed().subscribe({
      next: (selectedItems) => {
        if(selectedItems && selectedItems.length)
        {
          this.api.post<GreenhouseResponse>('/greenhouse/composter/feed', { food: selectedItems }).subscribe({
            next: r => {
              this.processGreenhouseResponse(r.data);
              this.waitingOnAJAX = false;
            },
            error: () => {
              this.waitingOnAJAX = false;
            }
          });
        }
        else
        {
          this.waitingOnAJAX = false;
        }
      }
    });
  }

  doMovePlant(plant: GreenhousePlantSerializationGroup, direction: number)
  {
    this.lastMovedPlant = plant.id;

    const plantIndex = this.allPlants.indexOf(plant);

    const swapWithOrdinal = this.allPlants[plantIndex + direction].ordinal;

    this.allPlants[plantIndex + direction].ordinal = plant.ordinal;
    plant.ordinal = swapWithOrdinal;

    this.allPlants = this.allPlants.sort(this.plantSort);
  }

  doSavePlantOrder()
  {
    if(this.waitingOnAJAX)
      return;

    if(!this.lastMovedPlant)
    {
      this.mode = 'tend';
      return;
    }

    this.lastMovedPlant = null;

    this.waitingOnAJAX = true;

    const orderData = this.allPlants.map(p => p.id);

    this.api.post('/greenhouse/updatePlantOrder', { order: orderData }).subscribe({
      next: () => {
        this.mode = 'tend';
        this.waitingOnAJAX = false;
      },
      error: () => {
        this.waitingOnAJAX = false;
      }
    })
  }

  doAssignHelper()
  {
    SelectPetDialog.open(this.matDialog)
      .afterClosed()
      .subscribe(pet => {
        if(pet)
        {
          this.waitingOnAJAX = true;

          this.api.post<GreenhouseResponse>('/greenhouse/assignHelper/' + pet.id).subscribe({
            next: r => {
              this.processGreenhouseResponse(r.data);
              this.waitingOnAJAX = false;
            },
            error: () => {
              this.waitingOnAJAX = false;
            }
          });
        }
      })
    ;
  }

  doRecallHelper()
  {
    if(this.waitingOnAJAX)
      return;

    this.waitingOnAJAX = true;

    this.api.post('/pet/' + this.greenhouse.helper.id + '/stopHelping').subscribe({
      next: _ => {
        this.greenhouse.helper = null;
        this.waitingOnAJAX = false;
      },
      error: _ => {
        this.waitingOnAJAX = false;
      }
    });
  }

  doViewHelper()
  {
    InteractWithAwayPetDialog.open(this.matDialog, this.greenhouse.helper.id, this.greenhouse.helper.name, [])
      .afterClosed()
      .subscribe({
        next: v => {
          if(v && v.newPet)
          {
            this.greenhouse.helper.name = v.newPet.name;
          }
        }
      })
    ;
  }
}

interface GreenhouseResponse
{
  greenhouse: MyGreenhouseSerializationGroup;
  weeds: string|null;
  plants: GreenhousePlantSerializationGroup[];
  fertilizer: FertilizerSerializationGroup[];
  hasBubblegum: boolean;
  hasOil: boolean;
}