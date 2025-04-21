import {Component, Input, OnChanges, OnDestroy, OnInit, SimpleChanges} from '@angular/core';
import * as d3 from 'd3';
import {Subscription} from "rxjs";
import {StorySectionModel} from "../../../adventure-editor/model/story-section.model";
import {GraphChangesService} from "../../service/graph-changes.service";
import {StoryChoiceActionSetStepModel} from "../../../adventure-editor/model/story-choice-action-set-step.model";

@Component({
    selector: 'app-vn-graph',
    template: '<div id="chart"></div>',
    styleUrls: ['./vn-graph.component.scss']
})
export class VnGraphComponent implements OnInit, OnChanges, OnDestroy {

  @Input() sections: StorySectionModel[];

  private nodes: any[];
  private links: any[];

  private chartDiv: any;
  private simulation;

  graphChangesSubscription: Subscription;

  constructor(private graphChanges: GraphChangesService) {}

  ngOnInit() {
    this.graphChangesSubscription = this.graphChanges.changes.subscribe({
      next: () => {
        this.buildSvg();
      }
    })
  }

  ngOnDestroy()
  {
    if(this.simulation)
      this.simulation.stop();

    this.graphChangesSubscription.unsubscribe();
  }

  ngOnChanges(changes: SimpleChanges)
  {
    this.buildSvg();
  }

  private buildNodesAndLinks()
  {
    console.log(this.sections);

    this.nodes = this.sections.map(s => {
      return {
        id: s.id,
      };
    });

    const sectionsWithChoices = this.sections.filter(s => s.choices && s.choices.length > 0);

    const links = sectionsWithChoices.map(s => {
      return {
        id: s.id,
        linksTo: s.choices.map(c => c.actions).filter(a => a.findIndex(a => a.type === 'setStep') >= 0).map(s => (<StoryChoiceActionSetStepModel[]>s)[0].step)
      };
    });

    this.links = links.reduce((total, link) => {
      return total.concat(link.linksTo.map(to => {
        return {
          source: link.id,
          target: to,
          value: 1
        };
      }));
    }, []);
  }

  private buildSvg()
  {
    this.buildNodesAndLinks();

    if(this.simulation)
      this.simulation.stop();

    this.simulation = d3.forceSimulation(this.nodes)
      .force("link", d3.forceLink(this.links).id(d => d.id))
      .force("charge", d3.forceManyBody())
      .force("center", d3.forceCenter(500, 500));

    this.chartDiv = d3.select('#chart');

    // remove existing svg, if it exists
    this.chartDiv.select('svg').remove();

    let svg = this.chartDiv.append('svg')
      .attr('viewBox', '0 0 1000 1000')
    ;

    const link = svg.append("g")
      .attr("stroke", "#999")
      .attr("stroke-opacity", 0.6)
      .selectAll("line")
      .data(this.links)
      .join("line")
      .attr("stroke-width", 1)
    ;

    const node = svg.append("g")
      .attr("stroke", "#fff")
      .attr("stroke-width", 1.5)
      .selectAll("g")
      .data(this.nodes)
      .join("g")
      .call(this.drag(this.simulation))
      .call(g => g.append('circle')
        .attr("r", 10)
        .attr("fill", '#6699cc')
      )
      .call(c => c.append('text')
        .text(d => d.id.toString())
        .attr('fill', '#000')
        .attr('stroke', '#000')
      )
    ;

    this.simulation.on("tick", () => {
      link
        .attr("x1", d => d.source.x)
        .attr("y1", d => d.source.y)
        .attr("x2", d => d.target.x)
        .attr("y2", d => d.target.y)
      ;

      node
        .attr('transform', d => 'translate(' + d.x + ', ' + d.y + ')')
      ;
    });
  }

  drag = simulation => {

    function dragstarted(event) {
      if (!event.active) simulation.alphaTarget(0.3).restart();
      event.subject.fx = event.subject.x;
      event.subject.fy = event.subject.y;
    }

    function dragged(event) {
      event.subject.fx = event.x;
      event.subject.fy = event.y;
    }

    function dragended(event) {
      if (!event.active) simulation.alphaTarget(0);
      event.subject.fx = null;
      event.subject.fy = null;
    }

    return d3.drag()
      .on("start", dragstarted)
      .on("drag", dragged)
      .on("end", dragended);
  }

}
