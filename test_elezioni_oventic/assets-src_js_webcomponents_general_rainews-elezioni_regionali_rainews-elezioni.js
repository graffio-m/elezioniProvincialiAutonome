import { html } from 'lit';

import { ElezioniBase } from './elezioni';

import styles from './rainews-elezioni-regionali-leaf.scss';


export class Elezioni extends ElezioniBase {

  static get properties() {
    return {
      enti: { type: Array },
      dataElezioni: { type: Object },
    };
  }

  constructor() {
    super();

    // Querystring
    this.qFase = this.fasi.includes(this.params.fase?.toLowerCase()) && this.params.fase?.toLowerCase() || null;
    this.qRegione = this.params.regione || '12';
    this.qCircoscrizione = this.params.circoscrizione || 'all';
    this.qComune = this.params.comune || 'all';

    // Instance variables
    this.dataElezioni = {};
    this.enti = [];
    this.circoscrizioni = [];
    this.comuni = [];

    styles.use();
  }

  connectedCallback() {
    super.connectedCallback();

    const qFaseIndex = this.fasi.findIndex(fase => fase === this.qFase);
    const admFaseIndex = this.fasi.findIndex(fase => fase === this.elezioniFase);

    this.fase = qFaseIndex > admFaseIndex ? this.elezioniFase : (this.qFase || this.elezioniFase);

    const indexFase = this.fasi.findIndex(fase => this.elezioniFase === fase);
    this.disabledFasi = this.fasi.map((fase, index) => index > indexFase ? true : false);

    this.getDate().then(() => {
      this.getEnti();
      this.getData();
    });
  }

  getEnti() {
    fetch(`/dl/rainews/elezioni2023/PX/getentiR/DE/${this.date}/TE/07/response.json`)
    .then(data => data.json())
    .then(data => {
      this.enti = data.enti;

      this.circoscrizioni = this.enti?.filter(ente => {
        const { RE } = this.getCode(ente.cod);
        return ente.tipo === 'CR' && RE === this.qRegione;
      }) || [];

      this.comuni = this.enti?.filter(ente => {
        const { RE, CR } = this.getCode(ente.cod);
        return ente.tipo === 'CM' && RE === this.qRegione && CR === this.getCode(this.qCircoscrizione).CR;
      }) || [];
    });
  }

  getData() {
    let url = `${this.getUrl()}RE/${this.qRegione}/`;

    if (this.qCircoscrizione !== 'all') {
      if (this.qComune !== 'all') {
        const { PR, CM } = this.getCode(this.qComune);
        url += `PR/${PR}/CM/${CM}`;
      } else {
        const { CR } = this.getCode(this.qCircoscrizione);
        url += `CR/${CR}`;
      }
    }

    fetch(`${url}/response.json`)
    .then(data => data.json())
    .then(data => this.dataElezioni = data)
    .catch(error => this.dataElezioni = {});
  }

  getRegionName() {
    switch (this.qRegione) {
      case '03': return 'Lombardia';
      case '04': return 'Trentino Alto Adige';
      case '12': return 'Lazio';
      default: return '';
    }
  }

  getCode(cod) {
    return {
      RE: cod.slice(0, 2),
      CR: cod.slice(2, 5),
      PR: cod.slice(5, 8),
      CM: cod.slice(8, 12),
    };
  }

  onChangeFase(fase, isDisabled) {
    if (isDisabled) return;
    this.goToLink(`?fase=${fase}&regione=${this.qRegione}`);
  }

  onChangeRegione() {
    if (this.qRegione === '03') this.goToLink(`?fase=${this.fase}&regione=12`);
    if (this.qRegione === '12') this.goToLink(`?fase=${this.fase}&regione=03`);
  }

  onChangeCircoscrizione(cod) {
    this.goToLink(`?fase=${this.fase}&regione=${this.qRegione}&circoscrizione=${cod}`);
  }

  onChangeComune(cod) {
    this.goToLink(`?fase=${this.fase}&regione=${this.qRegione}&circoscrizione=${this.qCircoscrizione}&comune=${cod}`);
  }

  getStatisticsData() {
    const statisticsData = this.dataElezioni?.int;

    switch (this.fase) {
      case 'exit poll':
        return [
          { label: 'Affluenza', value: this.getValueOr(statisticsData?.perc_vot, '--', '%')},
          { label: 'Elettori', value: this.getValueOr(statisticsData?.ele_t, '--') },
          { label: 'Votanti', value: this.getValueOr(statisticsData?.vot_t, '--') },
        ];

      case 'proiezioni':
        return [
          { label: 'Affluenza', value: this.getValueOr(statisticsData?.perc_vot, '--', '%') },
          { label: 'Elettori', value: this.getValueOr(statisticsData?.ele_t, '--') },
          { label: 'Votanti', value: this.getValueOr(statisticsData?.vot_t, '--') },
        ];

      case 'scrutini':
        return [
          { label: 'Affluenza', value: this.getValueOr(statisticsData?.perc_vot, '--', '%') },
          { label: 'Elettori', value: this.getValueOr(statisticsData?.ele_t, '--') },
          { label: 'Votanti', value: this.getValueOr(statisticsData?.vot_t, '--') },
          { label: 'Sezioni totali', value: this.getValueOr(statisticsData?.sz_tot, '--') },
          { label: 'Sezioni scrutinate', value: this.getValueOr(statisticsData?.sz_perv, '--') },
          { label: 'Schede bianche', value: this.getValueOr(statisticsData?.sk_bianche, '--') },
          { label: 'Schede nulle', value: this.getValueOr(statisticsData?.sk_nulle, '--') }
        ];

      default:
        return [];
    }
  }

  renderSimboli(partiti) {
    return html`
      <div class="simboli">
        ${partiti?.map(partito => html`
          <div><img class="simbolo" src="/dl/rainews/elezioni2023/simboli/DE/${this.date}/${partito?.img_lis_c || 'fallback.png'}" alt="${partito?.desc_lis_c}" /></div>
        `)}
      </div>
    `;
  }

  render() {
    return html`
      <header class="article__header">
      <h1 class="article__header__title">${this.data.titolo}</h1>

      <div class="article__header__selection-top">
        ${this.renderFasi()}
      </div>
    </header>

      <section class="elezioni-regionali-internal singola-circoscrizione">
        <div class="article__inner-header">
          <div class="inner-header__info">
            <h2 class="inner-header__info--title">${this.getRegionName()}</h2>
          </div>

          <div class="inner-header__links">
            <span class="links-change" @click="${this.onChangeRegione}">
              ${this.qRegione === '03' ? html`Vai a Lazio` : ''}
              ${this.qRegione === '12' ? html`Vai a Lombardia` : ''}
              <span class="icon-location"></span>
            </span>
          </div>
        </div>

        ${this.renderSelections()}

        <div class="main-wrapper scrutini">
          <section class="main-wrapper__section">
            ${this.fase === 'scrutini' && this.qComune !== 'all' ? html`
              <h3 class="section-title">${this.dataElezioni?.int?.desc_com}</h3>
            ` : html``}

            <div class="grid-x">
              <div class="cell medium-6 large-12 xlarge-9">
                <div class="riepilogo-results">
                  <div class="riepilogo">
                    <strong class="riepilogo__title">RISULTATI</strong>
                      <div class="custom-table-wrapper">
                        <div class="custom-table">
                          <div class="custom-table-row-group">
                            ${this.dataElezioni?.cand?.map((candidato, index) => html`
                              <div class="custom-table-row">
                                <input type="checkbox" class="accordion-button" id="accordion-${index}">

                                <div class="custom-table-cell">
                                  <div class="coalizione">
                                    <label class="accordion-label" for="accordion-${index}">
                                      <div class="nome-simboli">
                                        <h4 class="nome">${candidato.nome} ${candidato.cogn}</h4>
                                        ${this.renderSimboli(candidato.liste)}
                                      </div>

                                      <div class="percentuali-voti">
                                        ${this.renderPercentuali(candidato)}
                                        ${this.fase === 'scrutini' ? html`<div class="voti">${this.getValueOr(candidato.voti, '--', ' voti')}</div>` : html``}
                                      </div>
                                    </label>
                                  </div>
                                </div>

                                <div class="custom-table-cell">
                                  <div class="partiti-totale-seggi">
                                    <div class="partiti">
                                      <ul class="partiti__list">
                                        ${candidato.liste?.map(partito => html`
                                          <li class="partiti__list__item">
                                            <div class="simbolo-nome">
                                              <img class="simbolo" src="/dl/rainews/elezioni2023/simboli/DE/${this.date}/${partito?.img_lis_c || 'fallback.png'}" alt="${partito.desc_lis_c}" />
                                              <div class="nome">${partito.desc_lis_c}</div>
                                            </div>
                                            <div class="percentuali-voti">
                                              ${['proiezioni', 'scrutini'].includes(this.fase) ? html`<div class="percentuale">${this.getValueOr(partito.perc, '--', '%')}</div>` : html``}
                                              ${this.fase === 'scrutini' ? html`<div class="voti">${this.getValueOr(partito.voti, '--', ' voti')}</div>` : html``}
                                            </div>
                                          </li>
                                        `)}
                                      </ul>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            `)}
                          </div>
                        </div>
                      </div>
                  </div>
                </div>
              </div>

              <div class="cell medium-6 large-12 xlarge-3">
                <div class="results">
                  <div class="statistics">
                    <strong class="statistics__title">DATI STATISTICI</strong>

                    <dl class="statistics__list">
                      ${this.getStatisticsData().map(({ label, value }) => html`
                        <div class="statistics__list__item">
                          <dd class="key">${label}</dd>
                          <dt class="value">${value}</dt>
                        </div>
                      `)}
                    </dl>
                  </div>
                </div>
              </div>
            </div>
          </section>

          ${this.renderSourceUpdates(this.dataElezioni?.int)}
        </div>
      </section>
    `;
  }

  renderSelections() {
    if (this.fase !== 'scrutini') return html``;

    return html`
      <div class="article__header__selection-bottom">
        <div class="tipo-collegio">
          <div class="label">Scegli circoscrizione</div>

          <select id="tipo-collegio__selection" name="tipo-collegio__selection" class="tipo-collegio__selection" @change="${(e) => this.onChangeCircoscrizione(e.target.value)}">
            <option value="all">TUTTI</option>
            ${this.circoscrizioni?.map(circoscrizione => html`
              <option value="${circoscrizione.cod}" ?selected="${circoscrizione.cod === this.qCircoscrizione}">${circoscrizione.desc}</option>
            `)}
          </select>
        </div>

        ${this.qCircoscrizione === 'all' ? html`` : html`
          <div class="collegio">
            <div class="label">Scegli il comune</div>

            <select id="collegio__selection" name="collegio__selection" class="collegio__selection" @change="${(e) => this.onChangeComune(e.target.value)}">
              <option value="all">TUTTI</option>
              ${this.comuni?.map(comune => html`
                <option value="${comune.cod}" ?selected="${comune.cod === this.qComune}">${comune.desc}</option>
              `)}
            </select>
          </div>
        `}
      </div>
    `;
  }

}


if (!customElements.get('rainews-elezioni-regionali')) {
  customElements.define('rainews-elezioni-regionali', Elezioni);
}
