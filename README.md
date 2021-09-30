# metersen
Репозитарій відправки показників лічильників у обслуговуючі організації:

- подача показників елекрики через WebChat http://loe.lviv.ua/
- подача показників елекрики через EnergySuite.Online від розробників https://www.extracode.com.ua/ у
-     https://info.loe.lviv.ua/ - Львівобленерго
-     https://my.oe.if.ua/ - Прикарпаттяобленерго
-     https://esozoe.azurewebsites.net/ - ПАТ Запоріжжяобленерго
-     https://my.toe.com.ua/ - ВАТ Тернопільобленерго 
- подача показників газу https://104.ua/ через API (в процесі)
  Авторизація https://mobile.104.ua/billing/api/v2/users/username@gmail.com/sessions?password=12345678&device_id=1
  У відповідь отримуємо data:session_id
  Передавати заголовки:
    X-Application-Key: витягуємо із apk файлу
    X-Session-Id: session_id
  Отримати лічильники і подані показники:
    https://mobile.104.ua/billing/api/v2/users/username@gmail.com/meters
    https://mobile.104.ua/billing/api/v2/users/username@gmail.com/meters/meterages?created_at_range[start]=2020-12-22&created_at_range[end]=2021-03-22
  Передати показник:
    https://mobile.104.ua/billing/api/v2/users/username@gmail.com/meters/123456/meterages?created_at=2021-03-22%2020:47:59&value=1234.0

- подача показників води https://infolviv.com.ua/ (в процесі)
