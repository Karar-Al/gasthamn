/*
  Plugin Name: Boka
  @package Boka
*/

/**
 * BokaBlock ska använda sig av React.Component
 */
class BokaBlock extends React.Component {
  constructor (props) {
    // Skicka props från Wordpress till React.
    super(props)

    // Lägg till en state vi använder bara inom denna stund.
    // Alltså sparas inte detta på vår databas eller om vi startar om sidan.
    this.state = {
      serverRespons: '',
      bookings: []
    }

    this.getBookings()
  }
  /**
   * Alla react components kräver en render()
   */
  render () {
    // https://play.swc.rs/?version=1.2.151&code=H4sIAAAAAAAAA03PwU7DMAwG4DsS7xD1BJfmPqWRgBMHJMQeALmqt1V0iWW7VSfUx%2BFJeDGyepu4Wc6X33aI93fOha6f1sK5bz30UjOmDvk5568%2B7eXhcVmVv7JAFx2EIMXtBOx2%2FPuT3NP76yb4tXsRxBgtVBQUa0GekD9QKCdZgj%2B%2FW7qFhgFaHNwuc1MdYf4sTqWKbzA7GkDL9zJgNcZb9lb0iUa1oXoibCrFWStrJDji%2FzjrTjCM2NhyxJmkBlXu21FR6hteDOf0coC0v%2FqRunJN2Wp7M%2Bc9go9%2Fy7QAulMBAAA%3D&config=H4sIAAAAAAAAA0WMSw7DIBBD7%2BI123bBHXqIEZ1EVPw0Q6QixN0LbarsLPv5dbzUwXYUEmVZSVuq9IYFu0jqxJcKM7FZVTl4GFSSnesi9DankLMy7EZB2SD65Le2RC7HIqx6TZT28CfHFMX8PFbRUVvhr%2FCOcTnOn9fHCf6eH8%2FfGIu1AAAA
    return React.createElement(React.Fragment, null, React.createElement("div", null, this.renderBookings()), React.createElement("p", null, React.createElement("span", null, "Svar fr\xe5n API:"), React.createElement("pre", null, this.state.serverRespons)), React.createElement("label", {
        for: "max_spots"
    }, "Max platser:"), React.createElement("br", null), React.createElement("input", {
        type: "text",
        name: "max_spots",
        value: this.props.attributes.max_spots,
        onChange: this.updateMaxSpots
    }))
  }

  // Kör denna funktion när användaren matar in något i vår input.
  updateMaxSpots = (event) => {
    this.props.setAttributes({ max_spots: event.target.value })
  }

  getBookings = () => {
    // Gör en REST API request.
    wp.apiFetch({
      path: '/boka/v1/admin?action=data',
      method: 'POST'
    }).then(res => {
      this.setState({ bookings: res })
    })
  }

  renderBookings = () => {
    // https://play.swc.rs/?version=1.2.151&code=H4sIAAAAAAAAA02QSwrDMAxE94XeQWSThEJCt8HJEUqhvYBdq9TgT0jkQjC%2Be%2B18oDuNZvQGxN5uMuDswwujqA%2F0UXMjUSPhXfMXxuF8AmDCEzk7PDkINxFrd716mgvUkDh9MeaTYoCAlqalsdxghCqBaO4g3LwROFWbt0ZruMA11qxdGRtO2dET5BFgpkVjHwJINaeDpYPSOoslxLgncsXRu22UPPQe%2BXLtE%2BSvNm5GgnKhUWbVpnLW5mcMPx1Bc7oTAQAA&config=H4sIAAAAAAAAA0WMSw7DIBBD7%2BI123bBHXqIEZ1EVPw0Q6QixN0LbarsLPv5dbzUwXYUEmVZSVuq9IYFu0jqxJcKM7FZVTl4GFSSnesi9DankLMy7EZB2SD65Le2RC7HIqx6TZT28CfHFMX8PFbRUVvhr%2FCOcTnOn9fHCf6eH8%2FfGIu1AAAA
    return this.state.bookings.map((entry) =>
      React.createElement("form", {
          onSubmit: this.deletePlace
      }, React.createElement("button", null, "Ta bort"), React.createElement("label", {
          for: "place"
      }, " ", entry.name, " (Plats: ", Number(entry.place) + 1, ")"), React.createElement("input", {
          style: {
              display: 'none'
          },
          name: "place",
          id: "place",
          value: entry.place,
          disabled: true
      }))
    )
  }

  deletePlace = (event) => {
    event.preventDefault()

    console.log(event)

    const place = event.target.elements.place.value

    // Gör en REST API request.
    wp.apiFetch({
      path: '/boka/v1/admin',
      method: 'POST',
      data: { place }
    }).then(res => {
      this.setState({ serverRespons: res })
      this.getBookings()
    })
  }
}

wp.blocks.registerBlockType('boka/boka-block' /* "Computer readable" unika namnet. */, {
  title: 'Boka UI', // "Human readable" Namnet på blocket
  icon: 'book', // Ikoner från "Wordpress Dashicon"
  category: 'widgets', // Vilken kategori ska vårt block hamna under?
  attributes: {
    /**
     * Attribut vi vill ändra och hålla
     * koll på, dessa sparas i Wordpress.
     */
    max_spots: { type: 'string' }
  },
  /**
   * Rendera Gutenberg editor blocket när:
   *  vi drar in blocket
   *  vi markerar blocket
   *  vi slutar markera blocket
   */
  edit: function(props) {
    /**
     * props är ett objekt som har
     * innehåll från Wordpress & React
     */
    console.log(props)

    /**
     * `props.attributes` innehåller datan
     * vi har sparat sedan tidigare:
     */
    // console.log(props.attributes.max_spots)

    return React.createElement(BokaBlock, props, null)
  },
  /**
   * Körs när vi sparar inlägget.
   * (Se koden i plugin.php)
   */
  save: function () {
    /**
     * Vi skickar tillbaka `null` eftersom vi vill
     * generera innehållet på servern med PHP kod.
     */
    return null
  }
})
