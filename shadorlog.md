# Projekt Tevékenységnapló

Ez a napló részletesen dokumentálja a projekten töltött időmet, a dátumokat, valamint a végzett feladatokat. Célom ezzel a követhetőség és a teljes folyamat átláthatóságának biztosítása.

## Tevékenységek

| Dátum       | Időtartam | Tevékenység leírása |
|-------------|-----------|---------------------|
| 2024-11-11  | 1 óra     | A fiktív cég megalkotása, design-ja, hozzátartozó mock-up webshop elkészítése |
| 2024-11-12  | 5 óra     | Webshop fejlesztése és séma megtervezése |
| 2024-11-13  | 3 óra     | Megrendelő lap és back-end |
| 2024-11-14  | 3 óra     | Termék oldala és shopping cart prot |
| 2024-11-16  | 4 óra     | Termék oldala, shopping cart, kategóriák és redesign |
| 2024-11-18  | 1 óra     | Login, regisztráció sórán hitelesítás és cart fix |
| 2024-12-08  | 4 óra     | Checkout + E-mail |


## Összesített idő

Összesen eddig: **21 óra**

## Kiemelt célok

1. **Termékek feltöltése a kínlatba és annak rendelési folyamata** - Összeszedje a rendelő a neki szánt terméket majd annak a terméknek a rendelése és lebonyolítása.
2. **Beérkező rendelések kezelési rendszer** - Minden beérkező rendelés monitorozása és annak lebonyolításához tartozó fiktív management. (Feldolgozott rendelés küldösé a raktárba és logisztikába és annak folyamata).
3. **Admin panel** - Reszponzív dizájn és felhasználói jogosultságok kezelése.
4. **Email levelezés** - Kiküldje a megrendelőnek a rendelés részleteit.
5. **SQL Szerver felépítése és annak működése** - A webshop tudjon kommunikálni a telephelyen lévő SQL adadtbázis szerverrel. És annak folyamatos frissítése
6. **JSON HTTP POST** - A megrendelás egy JSON fájlt küldjön a telephelyekre melyel lehet monitorozni a rendeléseket
7. **DNS Szerver** - Egy szervergép hostoljon DNS szervert. rendeles.local-on lehessen követni a rendeléseket amit az SQL szerveren fog majd fetchelni.
8. **Felhőszolgáltatáson SQL szerver** UNAS shop kialakítása
9. **Linux server setup** Teljes dokumentáció készítése egy működő Linux szerverről
### DHCP 
### DNS
### HTTPS 
### Automatizált mentés
10. **Gant diagram készítése** Egy gant diagram vagy hasonló készítése, hogy demonstráljuk a belefektetett időt és energiát
 
## Teljesített célok

- [x] Linux: HTTPS :tada:
- [x] Webshop "skeletonja" :tada:
- [x] Regisztráció és bejelentkező rendszer, cookie, Email küldés :tada:
- [x] Termék feltöltése :tada:
- [x] Kosár :tada:
- [x] Kategóriák :tada:
- [x] Kész Design :tada:
- [x] Checkout + E-mail, rendelés adatai SQL-ben (local-on egyenlőre) :tada:
- [x] JSON fájl mentése :tada:

## Dokumentációs szövegek

### PixelForge

Cégünk alap elve, hogy a mai gamereket kitudjok minőségi termékekkel jó áron szolgálni. Idén alakult meg vállalkozásunk, melyet rengeteg munka árán sikerült véhgez vinni. Cégünk egyik fő tulajdonsága, hogy nem rugaszkodunk el a nagyker áraktól. Fő termékeink gamer hardverek (egerek, billentyűzetek, fejhallgatók, monitorok, egérpadok) melyeket a megrendelő gamereinknek csupán néhány munkanapon belül már házhoz is tudjuk szállítani és düböröghessen a ranked LOL, vagy akár egy pörgős FPS játék de szelídebb gamereink is megtalálják számukra optimális termékeket.

### Termék feltöltés

Termék feltöltése a shador.hu/vizsgaremek/feltoltes oldalon lehet megtenni. Ott meglehet adni a termék nevét, leírását, árát, kategóriáját, stock-ját (mennyi van készleten), és a hozzátartozó képeket lehet csatolni. Ezen az oldalon szintúgy lehet módosítani már feltöltött termékeket. Minden terméknek van egy id-ja ami alapján lehet az oldalon egyszerúbben és automatizáltan megjeleníteni és a rendelés során azonosítani a terméket.

### Checkout

Az oldalon működóképes a kosár és az abban lévő termékek kifizetése. A termékek adatai és a vásárló adatai a webshopot üzemeltető szerverén és a raktár fizikai szerverén lévő SQL adatbázisra menti el. Ennek segítségével, hogy 2 helyre menti el az adatokat, adatbiztonságot tudunk biztosítani, mivel a webshop szervere egy backup-ot nyújt míg a raktár szervere pedig csak is belső hálózaton történő hozzáférést biztosít. Így mind a raktári és logisztikai dolgozóink bizalmasan tudják kezelni vásárlóink adait és feldolgozni. A fizetés után az automatizált rendszerünk küld a vásárlónak egy visszaigazoló E-mailt ahol megtudja tekinteni az adatokat a rendelésről. 