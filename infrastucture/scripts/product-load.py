import boto3
import json
import uuid

ddb = boto3.resource('dynamodb', region_name='us-east-1')

table = ddb.Table('product-main')

response = table.put_item(
    Item={
        'ProductId': 'CS-001',
        'ProductName': 'French Press',
        'Description': 'The French press coffee maker is the simplest of all brewing systems, where coarsely ground beans meet hot water right off the boil. The right temperature brings the optimal extraction power for the essential oils in the beans to develop their full flavor profile in just four minutes. An easy press on the plunger locks the grinds at the bottom of the glass carafe and stops the brewing process.'
    }
)

response = table.put_item(
    Item={
        'ProductId': 'CS-002',
        'ProductName': 'Burr Coffee Grinder',
        'Description': 'Better coffee starts with a quality burr grinder. This grinder is built with stainless steel conical burrs to deliver consistent grind size for the optimal coffee extraction. Select from a range of 39 grind sizes for use in Espresso, Pour Over, Drip, Cold Brew, and French Press brewing methods. Adjust one grind size notch at a time to expose different flavors of your favorite coffee. Gain the ability to produce consistent results by precisely measuring your grind quantity.'
    }
)

response = table.put_item(
    Item={
        'ProductId': 'CS-003',
        'ProductName': 'Blade Coffee Grinder',
        'Description': 'Grind whole coffee beans quickly and efficiently with this powerful coffee grinder. A nice alternative to pre-ground coffee, brewing freshly ground beans promotes maximum rich aroma and delicious full-bodied flavor for better-tasting coffee. When making any type of coffee drink, from simple drip to espresso and cappuccino, grinding beans right before brewing is a must for any true coffee lover.'
    }
)

response = table.put_item(
    Item={
        'ProductId': 'CS-004',
        'ProductName': 'Drip Coffee Maker',
        'Description': 'This standard drip coffee maker with automatic pause is a lifesaver when you need a cup before the brew cycle is finished. It stops brewing so you can pour a rich-tasting cup and then finishes the brewing cycle after you place the carafe back in position. We put our best brewing forward with this easy-to-use coffee maker.'
    }
)

response = table.put_item(
    Item={
        'ProductId': 'CS-005',
        'ProductName': 'Espresso Maker',
        'Description': 'Create great tasting espresso in less than a minute. The Super Express allows you to grind the beans right before extraction, and its interchangeable filters and a choice of automatic or manual operation ensure authentic café style results in no time at all.'
    }
)

response = table.put_item(
    Item={
        'ProductId': 'CS-006',
        'ProductName': 'Iced Tea Maker',
        'Description': 'Easily brew fresh iced tea in the comfort of home with the 2-Quart Super Fresh Iced Tea Maker. One simple touch is all it takes to get brewing. Add water, ice, and tea bags or tea leaves, start the brew cycle and enjoy refreshing iced tea in about 10 minutes. The iced tea machine lets you create coffeehouse-inspired drinks easily by adding your own unique flavors, plus several recipes are included to help inspire delicious flavor mixes.'
    }
)

response = table.put_item(
    Item={
        'ProductId': 'CS-007',
        'ProductName': 'Pod Coffee Maker',
        'Description': 'The newest addition to the C-Pod single serve coffee maker family, the Corik c-select Coffee maker combines sleek design and more intuitive features to help you brew your perfect cup every single time. And for those who like a stronger cup of coffee, the Corik c-select brewer is the perfect choice. The new strong brew feature kicks up your coffee\'s strength and intensity, so you can enjoy a bolder brew.'
    }
)

response = table.put_item(
    Item={
        'ProductId': 'CS-008',
        'ProductName': 'Single Serve Coffee Maker',
        'Description': 'The One-Cup Coffeemaker goes where no coffeemaker has gone before, brewing hotter, faster and better-tasting coffee than most gourmet machines out there. And, its benefits don\'t stop there. The One-Cup Coffeemaker utilizes the simplicity of ground coffee and brews a customizable cup quickly: an 8 oz. cup in less than 90 seconds or a 14 oz. travel mug in under two-and-a-half minutes.'
    }
)

response = table.put_item(
    Item={
        'ProductId': 'CS-009',
        'ProductName': 'Mini Espresso Machine',
        'Description': 'With the espresso mini, le Shorti has delivered its most compact espresso machine yet - without any compromise on taste. Offering 2 programmable cup sizes, the Espresso Mini machine creates perfect coffee just the way you like it. Choose from 3 colors to fit your style and space. It\'s the small machine that opens up the whole world of espresso Coffee.'
    }
)

response = table.put_item(
    Item={
        'ProductId': 'CS-010',
        'ProductName': 'Pour Over Coffee Maker',
        'Description': 'This classic white ceramic dripper ranks among the best. This manual brewing method gives you complete control over brewing time and temperature, so your coffee is brewed just the way you like it.'
    }
)
