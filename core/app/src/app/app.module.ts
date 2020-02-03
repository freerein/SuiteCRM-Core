import {NgModule, CUSTOM_ELEMENTS_SCHEMA} from '@angular/core';
import {BrowserModule} from '@angular/platform-browser';
import {HttpClientModule} from '@angular/common/http';

import {AppRoutingModule} from './app-routing.module';
import {AppComponent} from './app.component';

import {NavbarUiModule} from '../components/navbar/navbar.module';
import {FooterUiModule} from '../components/footer/footer.module';
import {ClassicViewUiModule} from '../components/classic-view/classic-view.module';
import {MessageUiModule} from '../components/message/message.module';

import {BrowserAnimationsModule} from '@angular/platform-browser/animations';
import {NgbModule} from '@ng-bootstrap/ng-bootstrap';

import {
  AppManagerModule
} from '../app-manager/app-manager.module';

@NgModule({
  declarations: [
    AppComponent
  ],
  imports: [
    BrowserModule,
    HttpClientModule,
    AppManagerModule,
    AppRoutingModule,
    FooterUiModule,
    NavbarUiModule,
    MessageUiModule,
    ClassicViewUiModule,
    BrowserAnimationsModule,
    NgbModule
  ],
  bootstrap: [AppComponent],
  entryComponents: []
})
export class AppModule {
  constructor() {

  }
}