/*
  Plugin Name: Faciliteter
  @package Faciliteter
*/

/**
 * DemoTemplateBlock ska använda sig av React.Component
 */
class FaciliteterBlock extends React.Component {
  constructor (props) {
    // Skicka props från Wordpress till React.
    super(props)

    // Lägg till en state, detta är data vi använder bara inom denna stund.
    // Alltså sparas detta INTE på vår databas.
    this.state = {
      serverRespons: ''
    }

    // Kalla på denna funktion en gång för att få server data.
    this.getDataFromMYSQL()
  }

  render() {
    /**
     * Använd https://play.swc.rs/
     * för att skapa JS kod av JSX.
     ***************************************
     * Se nedanstående kod i JSX format här:
     * https://play.swc.rs/?version=1.2.151&code=H4sIAAAAAAAAA11Ru27DMAzcC%2FQfCI0erD2QDRSdu7QfUMgw0wixKYGSvBT5m%2BZL%2FGPVw06aLsSJPB4PJzWapX9%2BAlCTHnCCo%2BVO6BBY9G%2BGICMzxHBQsswrdWBZgSEXQ0YAUsKip4iw%2FjAsqYwYwNwE0IOYDX3ub1G3ykr3HU7Gt46t8%2B19of3Lv2xXmqYCaEBYej1p%2BkJRbh4jnYOxhATezuDPOrVpZA1uvd6WlvXKd1P%2FPLU7TVawH9gMRjfqgCmWl0dTpGfsHpXAjFuMmZLTKnm5%2FgN5QQZG7yz5Q2ox9lXeh6Te%2BkJ4r%2FOLknmealJQMv%2FVLwG2tHuxAQAA&config=H4sIAAAAAAAAA0WMSw7DIBBD7%2BI123bBHXqIEZ1EVPw0Q6QixN0LbarsLPv5dbzUwXYUEmVZSVuq9IYFu0jqxJcKM7FZVTl4GFSSnesi9DankLMy7EZB2SD65Le2RC7HIqx6TZT28CfHFMX8PFbRUVvhr%2FCOcTnOn9fHCf6eH8%2FfGIu1AAAA
     */
    return React.createElement("div", null, React.createElement("label", {
      for: "attr"
  }, "Facilitet att r\xf6sta f\xf6r:"), React.createElement("br", null), React.createElement("input", {
      value: this.props.attributes.facilitet,
      onChange: this.updateFacilitet,
      name: "facilitet",
      id: "attr"
  }), React.createElement("p", null, "Server respons: ", React.createElement("pre", null, this.state.serverRespons)));
  }

  /**
   * Varför
   * updateMinAttribut () => {}
   * och inte
   * updateMinAttribut () {}
   * 
   * Jo, för att vi vill att "this" för denna funktion
   * ska alltid vara bunden till denna instance.
   * Alltså kan vi använda den nu inom onChange.
   */
  updateFacilitet = (event) => {
    // Uppdatera attributen som ska skickas till servern.
    this.props.setAttributes({ facilitet: event.target.value })
  }

  getDataFromMYSQL = () => {
    // Skicka detta till REST API.
    wp.apiFetch({
      path: '/demo-template/v1/admin?action=data',
      method: 'POST'
    }).then(res => {
      this.setState({ serverRespons: res })
    })
  }
}

wp.blocks.registerBlockType('faciliteter/block' /* "Computer readable" unika namnet. */, {
  title: 'Faciliteter Block', // "Human readable" Namnet på blocket
  icon: 'star-filled', // Ikoner från "Wordpress Dashicon"
  category: 'widgets', // Vilken kategori ska vårt block hamna under?
  attributes: {
    /**
     * Attribut vi vill ändra och hålla
     * koll på, dessa sparas i Wordpress.
     */
    facilitet: { type: 'string' }
  },
  /**
   * Rendera Gutenberg editor blocket när:
   *  vi drar in blocket
   *  vi markerar blocket
   *  vi slutar markera blocket
   */
  edit: function (props) {
    /**
     * props är ett objekt som har
     * innehåll från Wordpress & React
     */
    console.log(props)
    /**
     * `props.attributes` innehåller datan
     * vi har sparat sedan tidigare:
     * `props.attributes.text`
     */
    // console.log(props.attributes.text)

    // Skicka alla props till vår DemoTemplateBlock class.
    return React.createElement(FaciliteterBlock, props, null)
  },
  /**
   * Körs när vi sparar inlägget.
   * (Se koden i plugin.php)
   */
  save: function () {
    /**
     * Vi skickar tillbaka `null` eftersom vi vill
     * generera innehållet med hjälp av servern i PHP kod.
     */
    return null
  }
})
