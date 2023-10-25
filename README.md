# Parkavimo sistema „ParkingLT“
## Uždavinio aprašymas
Projekto tikslas – palengvinti vairuotojams lengviau atrasti stovėjimo vietą tam tikroje miesto vietoje, taip sutrumpinant laiką ieškant laisvos vietos, kur gali palikti savo transporto priemonę.

Ši kuriama platforma ne tik padės taupyti vairuotojams laiką, bet ir padės sumažinti spūsčių susidarymo atvejus, ieškant laisvos stovėjimo vietos bei sumažins išmetamųjų teršalų kiekį.

Vairuotojas, norėdamas naudotis parkavimo sistema, nebūtinai turi prisiregistruoti prie sistemos. Neprisiregistravęs naudotojas galės matyti tuo momentu laisvas stovėjimo vietas, pamatyti kiek kainuoja tam tikroje zonoje palikti savo transporto priemonę. Prisiregistravęs naudotojas turės daugiau įvairių funkcijų. Galės apmokėti už stovėjimo vietą, matyti istoriją apie anksčiau pasinaudotą paslaugą, matyti gautas baudas dėl taisyklių pažeidimų parkavimo vietoje, prisidėti turimus automobilius, kuriais norės naudotis parkavimo zona, pridėti dokumentus dėl papildomų nuolaidų suteikimo (lengvata elektromobiliams, neįgalumo pažyma). Taip pat, pranešti problemas, su kuriomis susidūrė naudojantis sistema. Administratorius galės pridėti naujas parkavimo zonas, redaguoti jų informaciją, pridėti naujas stovėjimo vietas bei jas atnaujinti. Turės galimybę pridėti naudotojams baudas, patvirtinti vairuotojų dokumentus dėl suteikiamų lengvatų.

## 1.	Sistemos funkciniai reikalavimai
### Neregistruotas sistemos naudotojas galės:

- Matyti žemėlapyje esančias stovėjimo vietas;
- Peržiūrėti informaciją apie pasirinktą stovėjimo zoną;
- Matyti laisvas tuo metu stovėjimo vietas;
- Prisiregistruoti prie sistemos.

### Registruotas sistemos naudotojas galės:

- Prisijungti prie sistemos;
- Atsijungti nuo sistemos;
- Apsimokėti už stovėjimo vietą;
- Matyti istoriją apie anksčiau pasinaudotas paslaugas;
- Peržiūrėti gautas baudas;
- Apmokėti gautas baudas;
- Pridėti turimo automobilio informaciją;
- Atnaujinti informaciją;
- Pašalinti informaciją apie automobilį iš sistemos;
- Pridėti dokumentus dėl lengvatų suteikimo;
- Redaguoti paskyros informaciją;
- Pranešti problemas.

### Administratorius galės:

- Pridėti naujas parkavimo zonas;
- Redaguoti parkavimo zonos informaciją;
- Pašalinti parkavimo zoną;
- Pridėti parkavimo vietą;
- Redaguoti parkavimo vietos informaciją;
- Pašalinti parkavimo vietą iš sistemos;
- Pridėti baudas naudotojams;
- Redaguoti pridėtų baudų informaciją;
- Patvirtinti vairuotojų lengvatų dokumentus;
- Atsakyti gautas problemų užklausas.

## 2.	Sistemos architektūra
### Sistemos sudedamosios dalys:

-	Kliento pusė – bus realizuota naudojantis „Vue.js“
-	Serverio pusė – bus realizuota naudojant „PHP Laravel“;
- Duomenų bazė – „MySQL“.

Sistema bei visos sistemos dalys bus talpinamos „Azure“ serveryje. Internetinė aplikacija bus pasiekiama naudojantis „HTTPS“ protokolą. Siekiant užtikrinti sistemos veikimą, bus pasinaudota žemėlapių sistema „Mapbox“ API. Ši sistema turės veikti kartu su kliento puse (atvaizduoti žemėlapi) bei su serverio puse, kad būtų gaunami saugomi duomenys apie tam tikras zonas, bei papildomai nupiešiami ant žemėlapio. Žemiau matoma sistemos diegimo diagrama:

![Kuriamos sistemos diegimo diagrama](https://i.ibb.co/pKVQzTb/Screenshot-64.png)
